<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeColumn extends AbstractColumn {
    public function prepareContent($value): mixed {
        if (!$value instanceof \DateTime) {
            throw new \InvalidArgumentException("Value must be a DateTime object");
        }

        return $value->format($this->getOption("format"));
    }

    protected function configureOptions(OptionsResolver $resolver): void {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired("format")
            ->setAllowedTypes("format", "string");
    }
}
