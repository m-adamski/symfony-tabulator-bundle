<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;

class CallableColumn extends AbstractColumn {
    public function prepareContent($value): mixed {
        return call_user_func($this->getOption("callable"), $value);
    }

    protected function configureOptions(OptionsResolver $resolver): void {
        parent::configureOptions($resolver);

        $resolver->setRequired("callable")
            ->setAllowedTypes("callable", "callable");
    }
}
