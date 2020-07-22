<?php declare(strict_types=1);

namespace Tolkam\Permissions\Resource;

class CRUDResource extends Resource
{
    public const CREATE = 'create';
    public const READ   = 'read';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    
    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name, [
            self::CREATE,
            self::READ,
            self::UPDATE,
            self::DELETE,
        ]);
    }
}
