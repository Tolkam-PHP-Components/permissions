<?php declare(strict_types=1);

namespace Tolkam\Permissions\Role;

use InvalidArgumentException;

class Role implements RoleInterface
{
    /**
     * @var string
     */
    protected string $name;
    
    /**
     * @var Role[]
     */
    protected array $parents;
    
    /**
     * @param string $name
     * @param Role   ...$parents
     */
    public function __construct(string $name, Role ...$parents)
    {
        if (empty($name)) {
            throw new InvalidArgumentException(sprintf('Invalid role name "%s"', $name));
        }
        
        $this->name = $name;
        $this->parents = $parents;
    }
    
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * @inheritDoc
     */
    public function getParents(): array
    {
        return $this->parents;
    }
}
