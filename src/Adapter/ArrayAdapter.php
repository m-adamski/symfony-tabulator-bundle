<?php

namespace Adamski\Symfony\TabulatorBundle\Adapter;

use Adamski\Symfony\TabulatorBundle\AdapterQuery;
use Adamski\Symfony\TabulatorBundle\ArrayResult;
use Adamski\Symfony\TabulatorBundle\ResultInterface;
use Adamski\Symfony\TabulatorBundle\Sorter\SortingDirection;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArrayAdapter extends AbstractAdapter {
    public function getData(AdapterQuery $adapterQuery): ResultInterface {
        $adapterData = $this->getOption("data");

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
