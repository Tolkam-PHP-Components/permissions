<?php declare(strict_types=1);

namespace Tolkam\Permissions\Role;

interface RoleInterface
{
    /**
     * Gets the name
     *
     * @return string
     */
    public function getName(): string;
    
    /**
     * Gets the parents
     *
     * @return string[]
     */
    public function getParents(): array;
}
