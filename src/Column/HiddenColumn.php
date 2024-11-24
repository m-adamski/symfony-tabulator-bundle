<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;

class HiddenColumn extends AbstractColumn {
    public function prepareContent($value): mixed {
        return $value;
    }

    protected function getDefaultConfig(): array {
        return ["visible" => false];
    }
}
