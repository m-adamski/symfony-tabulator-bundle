<?php

namespace Adamski\Symfony\TabulatorBundle\Sorter;

use Adamski\Symfony\TabulatorBundle\Column\AbstractColumn;

class SortingItem {
    private AbstractColumn $column;
    private SortingDirection $direction;

    public function getColumn(): AbstractColumn {
        return $this->column;
    }

    public function setColumn(AbstractColumn $column): SortingItem {
        $this->column = $column;
        return $this;
    }

    public function getDirection(): SortingDirection {
        return $this->direction;
    }

    public function setDirection(SortingDirection $direction): SortingItem {
        $this->direction = $direction;
        return $this;
    }
}
