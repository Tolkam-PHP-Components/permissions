<?php declare(strict_types=1);

namespace Tolkam\Permissions\Resource;

interface ResourceInterface
{
    /**
     * Gets the name
     *
     * @return string
     */
    public function getName(): string;
    
    /**
     * Gets the actions
     *
     * @return array
     */
    public function getActions(): array;
}
