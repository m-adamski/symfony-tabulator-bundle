<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;

class TextColumn extends AbstractColumn {
    public function prepareContent($value): mixed {
        return $value;
    }
}
