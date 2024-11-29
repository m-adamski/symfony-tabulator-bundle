<?php

namespace Adamski\Symfony\TabulatorBundle\Adapter;

use Adamski\Symfony\TabulatorBundle\AdapterQuery;
use Adamski\Symfony\TabulatorBundle\ArrayResult;
use Adamski\Symfony\TabulatorBundle\Filter\FilteringComparison;
use Adamski\Symfony\TabulatorBundle\Filter\FilteringType;
use Adamski\Symfony\TabulatorBundle\ResultInterface;
use Adamski\Symfony\TabulatorBundle\Sorter\SortingDirection;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArrayAdapter extends AbstractAdapter {
    public function getData(AdapterQuery $adapterQuery): ResultInterface {
        $adapterData = $this->getOption("data");

        // Process filtering
        if ($adapterQuery->getFilteringBag()->hasFiltering()) {
            $adapterData = array_filter($adapterData, function (array $item) use ($adapterQuery) {
                $filterAndResult = true;
                $filterOrResult = count($adapterQuery->getFilteringBag()->getFilters(FilteringComparison::OR)) <= 0;

                foreach ($adapterQuery->getFilteringBag()->getFilters(FilteringComparison::AND) as $filterItem) {
                    if ($filterItem->getType() === FilteringType::EQUAL) {
                        $filterAndResult &= $item[$filterItem->getColumn()->getOption("field")] == $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::NOT_EQUAL) {
                        $filterAndResult &= $item[$filterItem->getColumn()->getOption("field")] != $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::GREATER) {
                        $filterAndResult &= $item[$filterItem->getColumn()->getOption("field")] > $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::GREATER_OR_EQUAL) {
                        $filterAndResult &= $item[$filterItem->getColumn()->getOption("field")] >= $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::LESS) {
                        $filterAndResult &= $item[$filterItem->getColumn()->getOption("field")] < $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::LESS_OR_EQUAL) {
                        $filterAndResult &= $item[$filterItem->getColumn()->getOption("field")] <= $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::STARTS_WITH) {
                        $filterAndResult &= str_starts_with($item[$filterItem->getColumn()->getOption("field")], $filterItem->getValue());
                    } else if ($filterItem->getType() === FilteringType::ENDS_WITH) {
                        $filterAndResult &= str_ends_with($item[$filterItem->getColumn()->getOption("field")], $filterItem->getValue());
                    } else if ($filterItem->getType() === FilteringType::LIKE) {
                        $filterAndResult &= str_contains($item[$filterItem->getColumn()->getOption("field")], $filterItem->getValue());
                    } else {
                        throw new \InvalidArgumentException("Provided filtering type is not supported by the ArrayAdapter");
                    }
                }

                foreach ($adapterQuery->getFilteringBag()->getFilters(FilteringComparison::OR) as $filterItem) {
                    if ($filterItem->getType() === FilteringType::EQUAL) {
                        $filterOrResult |= $item[$filterItem->getColumn()->getOption("field")] == $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::NOT_EQUAL) {
                        $filterOrResult |= $item[$filterItem->getColumn()->getOption("field")] != $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::GREATER) {
                        $filterOrResult |= $item[$filterItem->getColumn()->getOption("field")] > $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::GREATER_OR_EQUAL) {
                        $filterOrResult |= $item[$filterItem->getColumn()->getOption("field")] >= $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::LESS) {
                        $filterOrResult |= $item[$filterItem->getColumn()->getOption("field")] < $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::LESS_OR_EQUAL) {
                        $filterOrResult |= $item[$filterItem->getColumn()->getOption("field")] <= $filterItem->getValue();
                    } else if ($filterItem->getType() === FilteringType::STARTS_WITH) {
                        $filterOrResult |= str_starts_with($item[$filterItem->getColumn()->getOption("field")], $filterItem->getValue());
                    } else if ($filterItem->getType() === FilteringType::ENDS_WITH) {
                        $filterOrResult |= str_ends_with($item[$filterItem->getColumn()->getOption("field")], $filterItem->getValue());
                    } else if ($filterItem->getType() === FilteringType::LIKE) {
                        $filterOrResult |= str_contains($item[$filterItem->getColumn()->getOption("field")], $filterItem->getValue());
                    } else {
                        throw new \InvalidArgumentException("Provided filtering type is not supported by the ArrayAdapter");
                    }
                }

                return $filterAndResult && $filterOrResult;
            });
        }

        // Process sorting
        if ($adapterQuery->getSortingBag()->hasSorting()) {
            foreach ($adapterQuery->getSortingBag()->getSortingItems() as $sortingItem) {
                $sortingItem->getDirection() === SortingDirection::ASC ? sort($adapterData) : rsort($adapterData);
            }
        }

        // Process pagination
        if ($adapterQuery->isPagination()) {
            $totalRecords = count($adapterData);
            $totalPages = ceil($totalRecords / $adapterQuery->getPaginationSize());
            $data = array_slice($adapterData, ($adapterQuery->getPaginationPage() * $adapterQuery->getPaginationSize()) - $adapterQuery->getPaginationSize(), $adapterQuery->getPaginationSize());

            return new ArrayResult($data, $totalPages, $totalRecords);
        }

        return new ArrayResult($adapterData);
    }

    protected function configureOptions(OptionsResolver $resolver): void {
        $resolver->setRequired(["data"])
            ->setAllowedTypes("data", "array");
    }
}
