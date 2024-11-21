<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;

class TextColumn extends AbstractColumn {
    protected function configureOptions(OptionsResolver $resolver): void {
        $resolver->setRequired(["title", "field"])
            ->setAllowedTypes("title", "string")
            ->setAllowedTypes("field", "string")
            ->setIgnoreUndefined();
    }
}
