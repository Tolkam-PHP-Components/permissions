<?php declare(strict_types=1);

namespace Tolkam\Permissions;

interface PermissionAwareInterface
{
    /**
     * Checks if role has specific permission
     *
     * @param string $roleName
     * @param string $action
     * @param string $resourceName
     *
     * @return bool
     */
    public function can(string $roleName, string $action, string $resourceName): bool;
}
