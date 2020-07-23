<?php declare(strict_types=1);

namespace Tolkam\Permissions;

use Tolkam\Permissions\Permission\PermissionInterface;
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
     * @var PermissionInterface[]
     */
    protected array $permissions = [];
    
    /**
     * @var string[]
     */
    private array $compiled = [];
    
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
        $path = $this->buildPath($roleName, $action, $resourceName);
        if (isset($this->compiled[$path])) {
            return true;
        }
        
        // check parents
        foreach ($role->getParents() as $parent) {
            $path = $this->buildPath($parent, $action, $resourceName);
            if (isset($this->compiled[$path])) {
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
            $p = $role->getParents();
            foreach ($p as $parent) {
                $parents[$role->getName()] = array_merge(
                    [$parent],
                    $parents[$parent] ?? []
                );
            }
        }
        
        foreach ($this->permissions as $permission) {
            $roleName = $permission->getRoleName();
            $resourceName = $permission->getResourceName();
            $permissionActions = $permission->getActions();
            
            if (!$role = $this->getRole($roleName)) {
                throw new PermissionsManagerException(sprintf(
                    'No "%s" role is registered',
                    $roleName
                ));
            }
            
            if (!$resource = $this->getResource($resourceName)) {
                throw new PermissionsManagerException(sprintf(
                    'No "%s" resource is registered',
                    $resourceName
                ));
            }
            
            $resourceActions = $resource->getActions();
            if (!empty($permissionActions)) {
                if ($invalid = array_diff($permissionActions, $resourceActions)) {
                    throw new PermissionsManagerException(sprintf(
                        'Some actions ("%s") were not found on resource "%s"',
                        implode('", "', $invalid),
                        $resourceName
                    ));
                }
            }
            else {
                // use all available actions if specific actions are not provided
                $permissionActions = $resourceActions;
            }
            
            // build own permission path
            $this->compilePaths($roleName, $resourceName, $permissionActions);
            
            // build inherited permissions paths
            foreach ($parents as $r => $p) {
                if ($r !== $roleName) { // exclude self
                    foreach ($p as $parentName) {
                        if ($parentName === $roleName) {
                            $this->compilePaths($r, $resourceName, $permissionActions);
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
     * @param PermissionInterface[] $permissions
     *
     * @return $this
     */
    public function addPermissions(PermissionInterface ...$permissions): self
    {
        foreach ($permissions as $permission) {
            $this->permissions[] = $permission;
        }
        
        return $this;
    }
    
    /**
     * @param string $name
     *
     * @return RoleInterface|null
     */
    public function getRole(string $name): ?RoleInterface
    {
        return $this->roles[$name] ?? null;
    }
    
    /**
     * @param string $name
     *
     * @return ResourceInterface|null
     */
    public function getResource(string $name): ?ResourceInterface
    {
        return $this->resources[$name] ?? null;
    }
    
    /**
     * @param string $roleName
     * @param string $resourceName
     * @param array  $actions
     */
    private function compilePaths(
        string $roleName,
        string $resourceName,
        array $actions
    ) {
        foreach ($actions as $action) {
            $path = $this->buildPath($roleName, $action, $resourceName);
            $this->compiled[$path] = true;
        }
    }
    
    /**
     * Builds permission path
     *
     * @param string $role
     * @param string $action
     * @param string $resource
     *
     * @return string
     */
    private function buildPath(string $role, string $action, string $resource)
    {
        return $role . '.' . $action . '_' . $resource;
    }
}
