<?php declare(strict_types=1);

namespace Tolkam\Permissions;

interface PermissionsConfiguratorInterface
{
    /**
     * @param PermissionsManager $manager
     *
     * @return void
     */
    public function configure(PermissionsManager $manager): void;
}
