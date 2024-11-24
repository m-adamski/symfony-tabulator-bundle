<?php

namespace Adamski\Symfony\TabulatorBundle\Sorter;

class SortingBag {
    private array $sortingItems = [];

    public function addSortingItem(SortingItem $sortingItem): SortingBag {
        $this->sortingItems[] = $sortingItem;
        return $this;
    }

    /**
     * @return SortingItem[]
     */
    public function getSortingItems(): array {
        return $this->sortingItems;
    }

    public function hasSorting(): bool {
        return count($this->sortingItems) > 0;
    }
}
