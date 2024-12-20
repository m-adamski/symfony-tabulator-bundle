<?php

namespace Adamski\Symfony\TabulatorBundle\Filter;

class FilteringBag {
    private array $filters = [
        FilteringComparison::AND->value => [],
        FilteringComparison::OR->value  => []
    ];

    public function addFilter(FilteringItem $item, FilteringComparison $comparison): void {
        $this->filters[$comparison->value][] = $item;
    }

    /**
     * @param FilteringComparison|null $comparison
     * @return FilteringItem[]|FilteringItem[][]
     */
    public function getFilters(?FilteringComparison $comparison = null): array {
        return null !== $comparison ? $this->filters[$comparison->value] : $this->filters;
    }

    public function hasFiltering(): bool {
        return count($this->filters[FilteringComparison::AND->value]) > 0 || count($this->filters[FilteringComparison::OR->value]) > 0;
    }
}
