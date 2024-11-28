<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

class HiddenColumn extends AbstractColumn {
    public function prepareContent($value): mixed {
        return $value;
    }

    protected function getDefaultConfig(): array {
        return ["visible" => false];
    }
}
