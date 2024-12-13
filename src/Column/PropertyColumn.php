<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PropertyColumn extends AbstractColumn {
    public function prepareContent($value): mixed {
        if (null === $value) {
            return $this->getOption("nullValue");
        }

        // https://symfony.com/doc/current/components/property_access.html
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        return $propertyAccessor->getValue($value, $this->getOption("property")) ?? $this->getOption("nullValue");
    }

    protected function configureOptions(OptionsResolver $resolver): void {
        parent::configureOptions($resolver);

        $resolver->setRequired("property")
            ->setDefaults(["nullValue" => null])
            ->setAllowedTypes("property", "string")
            ->setAllowedTypes("nullValue", ["string", "null"]);
    }
}
