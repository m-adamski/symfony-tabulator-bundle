<?php

namespace Adamski\Symfony\TabulatorBundle\Adapter;

use Adamski\Symfony\TabulatorBundle\AdapterQuery;
use Adamski\Symfony\TabulatorBundle\ResultInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CallableAdapter extends AbstractAdapter {
    public function getData(AdapterQuery $adapterQuery): ResultInterface {
        $functionResult = $this->getOption("function")($adapterQuery);

        if (!$functionResult instanceof ResultInterface) {
            throw new \LogicException("The callable function must return an instance of ResultInterface");
        }

        return $functionResult;
    }

    protected function configureOptions(OptionsResolver $resolver): void {
        $resolver->setRequired(["function"])
            ->setAllowedTypes("function", "callable");
    }
}
