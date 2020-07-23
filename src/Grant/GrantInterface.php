<?php declare(strict_types=1);

namespace Tolkam\Permissions\Grant;

interface GrantInterface
{
    /**
     * Gets the roleName
     *
     * @return string
     */
    public function getRoleName(): string;
    
    /**
     * Gets the resourceName
     *
     * @return string
     */
    public function getResourceName(): string;
    
    /**
     * Gets the actions
     *
     * @return array
     */
    public function getActions(): array;
}
