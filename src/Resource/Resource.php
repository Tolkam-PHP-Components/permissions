<?php declare(strict_types=1);

namespace Tolkam\Permissions\Resource;

class Resource implements ResourceInterface
{
    /**
     * @var string
     */
    protected string $name;
    
    /**
     * @var array
     */
    protected array $actions;
    
    /**
     * @param string $name
     * @param array  $actions
     */
    public function __construct(string $name, array $actions)
    {
        $this->name = $name;
        $this->actions = $actions;
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
    public function getActions(): array
    {
        return $this->actions;
    }
}
