<?php

namespace Adamski\Symfony\TabulatorBundle\Filter;

use Adamski\Symfony\TabulatorBundle\Column\AbstractColumn;

class FilteringItem {
    private ?AbstractColumn $column = null;
    private FilteringType $type;
    private mixed $value;

    public function getColumn(): ?AbstractColumn {
        return $this->column;
    }

    public function setColumn(?AbstractColumn $column): FilteringItem {
        $this->column = $column;
        return $this;
    }

    public function getType(): FilteringType {
        return $this->type;
    }

    public function setType(FilteringType $type): FilteringItem {
        $this->type = $type;
        return $this;
    }

    public function getValue(): mixed {
        return $this->value;
    }

    public function setValue(mixed $value): FilteringItem {
        $this->value = $value;
        return $this;
    }
}
