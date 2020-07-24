<?php declare(strict_types=1);

namespace Tolkam\Permissions;

use Tolkam\Permissions\Grant\GrantInterface;
use Tolkam\Permissions\Resource\ResourceInterface;
use Tolkam\Permissions\Role\RoleInterface;

class PermissionsManager implements PermissionAwareInterface
{
    /**
     * @var RoleInterface[]
     */
    protected array $roles = [];
    
    /**
     * @var ResourceInterface[]
     */
    protected array $resources = [];
    
    /**
     * @var GrantInterface[]
     */
    protected array $grants = [];
    
    /**
     * @var array
     */
    private array $permissions = [];
    
    /**
     * @var bool
     */
    private bool $isCompiled = false;
    
    /**
     * @inheritDoc
     */
    public function can(string $roleName, string $action, string $resourceName): bool
    {
        if (!$this->isCompiled) {
            throw new PermissionsManagerException('Permissions must be compiled first');
        }
        
        $role = $this->roles[$roleName] ?? null;
        
        // no role
        if ($role === null) {
            return false;
        }
        
        // check own permissions first
        if (isset($this->permissions[$roleName][$resourceName][$action])) {
            return true;
        }
        
        // check parents
        foreach ($role->getParents() as $parent) {
            if (isset($this->permissions[$parent][$resourceName][$action])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @return bool
     */
    public function isCompiled(): bool
    {
        return $this->isCompiled;
    }
    
    /**
     * Compiles permissions
     *
     * @return void
     */
    public function compile(): void
    {
        if ($this->isCompiled()) {
            throw new PermissionsManagerException('Permissions already compiled');
        }
        
        // flatten parents
        $parents = [];
        foreach ($this->roles as $role) {
            $r = $role->getName();
            $p = $role->getParents();
            foreach ($p as $parent) {
                $parents[$r] = array_merge($parents[$r] ?? [], $parents[$parent] ?? [], [$parent]);
            }
        }
        
        foreach ($this->grants as $grant) {
            $roleName = $grant->getRoleName();
            $resourceName = $grant->getResourceName();
            $grantActions = $grant->getActions();
            
            if (!$role = $this->roles[$roleName] ?? null) {
                throw new PermissionsManagerException(sprintf(
                    'No "%s" role is registered',
                    $roleName
                ));
            }
            
            if (!$resource = $this->resources[$resourceName] ?? null) {
                throw new PermissionsManagerException(sprintf(
                    'No "%s" resource is registered',
                    $resourceName
                ));
            }
            
            $resourceActions = $resource->getActions();
            if (!empty($grantActions)) {
                if ($invalid = array_diff($grantActions, $resourceActions)) {
                    throw new PermissionsManagerException(sprintf(
                        'Some actions ("%s") were not found on resource "%s"',
                        implode('", "', $invalid),
                        $resourceName
                    ));
                }
            }
            else {
                // use all available actions if specific actions are not provided
                $grantActions = $resourceActions;
            }
            
            // set own permissions
            $this->setPermissions($roleName, $resourceName, $grantActions);
            
            // set inherited permissions
            foreach ($parents as $r => $p) {
                if ($r !== $roleName) { // exclude self
                    foreach ($p as $parentName) {
                        if ($parentName === $roleName) {
                            $this->setPermissions($r, $resourceName, $grantActions);
                        }
                    }
                }
            }
        }
        
        $this->isCompiled = true;
    }
    
    /**
     * @param RoleInterface[] $roles
     *
     * @return $this
     */
    public function addRoles(RoleInterface ...$roles): self
    {
        foreach ($roles as $role) {
            $this->roles[$role->getName()] = $role;
        }
        
        return $this;
    }
    
    /**
     * @param ResourceInterface[] $resources
     *
     * @return $this
     */
    public function addResources(ResourceInterface ...$resources): self
    {
        foreach ($resources as $resource) {
            $this->resources[$resource->getName()] = $resource;
        }
        
        return $this;
    }
    
    /**
     * @param GrantInterface[] $grants
     *
     * @return $this
     */
    public function addGrants(GrantInterface ...$grants): self
    {
        foreach ($grants as $grant) {
            $this->grants[] = $grant;
        }
        
        return $this;
    }
    
    /**
     * Gets the permissions
     *
     * @param string|null $roleName
     *
     * @return array
     */
    public function getPermissions(string $roleName = null): array
    {
        return $roleName
            ? $this->permissions[$roleName] ?? []
            : $this->permissions;
    }
    
    /**
     * @param string $roleName
     * @param string $resourceName
     * @param array  $actions
     */
    private function setPermissions(
        string $roleName,
        string $resourceName,
        array $actions
    ) {
        foreach ($actions as $action) {
            $this->permissions[$roleName][$resourceName][$action] = true;
        }
    }
}
