<?php

namespace Adamski\Symfony\TabulatorBundle\Parser;

use Adamski\Symfony\TabulatorBundle\Column\AbstractColumn;

interface ParserInterface {

    /**
     * @param array            $adapterData
     * @param AbstractColumn[] $columns
     * @return array
     */
    public function parse(array $adapterData, array $columns): array;
}
