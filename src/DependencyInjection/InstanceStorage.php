<?php

namespace Adamski\Symfony\TabulatorBundle\DependencyInjection;

use Adamski\Symfony\TabulatorBundle\Adapter\AbstractAdapter;
use Adamski\Symfony\TabulatorBundle\Column\AbstractColumn;

class InstanceStorage {
    public function __construct(
        private readonly array $instances = []
    ) {}

    public function getAdapter(string $type): AbstractAdapter {
        return $this->getInstance($type, AbstractAdapter::class);
    }

    public function getColumn(string $type): AbstractColumn {
        return $this->getInstance($type, AbstractColumn::class);
    }

    private function getInstance(string $type, string $baseType) {
        if (isset($this->instances[$baseType]) && $this->instances[$baseType]->has($type)) {
            $instance = clone $this->instances[$baseType]->get($type);
        } elseif (class_exists($type)) {
            $instance = new $type();
        } else {
            throw new \InvalidArgumentException(sprintf('Could not resolve type "%s" to a service or class', $type, $baseType));
        }

        if (!$instance instanceof $baseType) {
            throw new \InvalidArgumentException(sprintf('Class "%s" must implement/extend %s', $type, $baseType));
        }

        return $instance;
    }
}
