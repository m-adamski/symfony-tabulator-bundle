<?php

namespace Adamski\Symfony\TabulatorBundle\Adapter;

use Adamski\Symfony\TabulatorBundle\AdapterQuery;
use Adamski\Symfony\TabulatorBundle\ArrayResult;
use Adamski\Symfony\TabulatorBundle\ResultInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArrayAdapter extends AbstractAdapter {
    public function getData(AdapterQuery $adapterQuery): ResultInterface {
        if ($adapterQuery->isPagination()) {
            $totalRecords = count($this->getOptions()["data"]);
            $totalPages = ceil($totalRecords / $adapterQuery->getPaginationSize());
            $data = array_slice(
                $this->getOptions()["data"],
                ($adapterQuery->getPaginationPage() * $adapterQuery->getPaginationSize()) - $adapterQuery->getPaginationSize(),
                $adapterQuery->getPaginationSize()
            );

            return new ArrayResult($data, $totalPages, $totalRecords);
        }

        return new ArrayResult($this->getOptions()["data"]);
    }

    protected function configureOptions(OptionsResolver $resolver): void {
        $resolver->setRequired(["data"])
            ->setAllowedTypes("data", "array");
    }
}
