<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

class TextColumn extends AbstractColumn {
    public function prepareContent($value): mixed {
        return $value;
    }
}
