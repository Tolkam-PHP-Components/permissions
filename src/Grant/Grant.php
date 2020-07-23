<?php declare(strict_types=1);

namespace Tolkam\Permissions\Grant;

class Grant implements GrantInterface
{
    /**
     * @var string
     */
    protected string $roleName;
    
    /**
     * @var string
     */
    protected string $resourceName;
    
    /**
     * @var array
     */
    protected array $actions;
    
    /**
     * @param string $roleName
     * @param string $resourceName
     * @param array  $actions
     */
    public function __construct(string $roleName, string $resourceName, array $actions)
    {
        $this->roleName = $roleName;
        $this->resourceName = $resourceName;
        $this->actions = $actions;
    }
    
    /**
     * @inheritDoc
     */
    public function getRoleName(): string
    {
        return $this->roleName;
    }
    
    /**
     * @inheritDoc
     */
    public function getResourceName(): string
    {
        return $this->resourceName;
    }
    
    /**
     * @inheritDoc
     */
    public function getActions(): array
    {
        return $this->actions;
    }
}
