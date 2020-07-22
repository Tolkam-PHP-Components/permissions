<?php declare(strict_types=1);

namespace Tolkam\Permissions;

use Tolkam\Permissions\Resource\ResourceInterface;
use Tolkam\Permissions\Role\RoleInterface;

class PermissionsManager
{
    protected const SEP_ROLE       = '.';
    protected const SEP_PERMISSION = '_';
    
    /**
     * @var RoleInterface[]
     */
    protected array $roles = [];
    
    /**
     * @var ResourceInterface[]
     */
    protected array $resources = [];
    
    /**
     * @var string[]
     */
    protected array $permissions = [];
    
    /**
     * @param PermissionsConfiguratorInterface ...$configurators
     */
    public function __construct(PermissionsConfiguratorInterface ...$configurators)
    {
        foreach ($configurators as $configurator) {
            $configurator->configure($this);
        }
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
     * @param RoleInterface     $role
     * @param ResourceInterface $resource
     * @param array             $actions
     *
     * @return $this
     * @throws PermissionsManagerException
     */
    public function addPermissions(
        RoleInterface $role,
        ResourceInterface $resource,
        array $actions = []
    ): self {
        
        $roleName = $role->getName();
        $resourceName = $resource->getName();
        $resourceActions = $resource->getActions();
        
        if (!$this->getRole($roleName)) {
            $this->addRoles($role);
        }
        
        if (!$this->getResource($resourceName)) {
            $this->addResources($resource);
        }
        
        if (!empty($actions)) {
            if ($invalid = array_diff($actions, $resourceActions)) {
                throw new PermissionsManagerException(sprintf(
                    'Some actions ("%s") were not found on resource "%s"',
                    implode('", "', $invalid),
                    $resourceName
                ));
            }
        }
        else {
            // use all available actions if specific actions are not provided
            $actions = $resourceActions;
        }
        
        foreach ($actions as $action) {
            $path = $this->path($roleName, $action, $resourceName);
            $this->permissions[$path] = true;
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
     * @param string $action
     * @param string $resourceName
     *
     * @return bool
     */
    public function can(string $roleName, string $action, string $resourceName): bool
    {
        $role = $this->roles[$roleName] ?? null;
        
        // no role
        if ($role === null) {
            return false;
        }
        
        // check role first
        foreach ($role->getParents() as $parent) {
            $path = $this->path($parent->getName(), $action, $resourceName);
            if (isset($this->permissions[$path])) {
                return true;
            }
        }
        
        return isset($this->permissions[$this->path($roleName, $action, $resourceName)]);
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
    private function path(string $role, string $action, string $resource)
    {
        return $role . self::SEP_ROLE . $action . self::SEP_PERMISSION . $resource;
    }
}
