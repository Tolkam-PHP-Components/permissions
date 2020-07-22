<?php declare(strict_types=1);

namespace Tolkam\Permissions\Resource;

class CRUDResource extends Resource
{
    public const CREATE = 'C';
    public const READ   = 'R';
    public const UPDATE = 'U';
    public const DELETE = 'D';
    
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
