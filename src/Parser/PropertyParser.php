<?php

namespace Adamski\Symfony\TabulatorBundle\Parser;

use Adamski\Symfony\TabulatorBundle\Column\TwigColumn;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PropertyParser implements ParserInterface {
    public function parse(array $adapterData, array $columns): array {
        $propertyAccess = PropertyAccess::createPropertyAccessor();

        $parseResult = [];
        foreach ($adapterData as $dataItem) {

            $columnResult = [];
            foreach ($columns as $name => $column) {
                $columnName = $column->getOption("field");
                $propertyPath = is_array($dataItem) ? "[" . $columnName . "]" : $columnName;

                $columnResult[$columnName] = $column->prepareContent(
                    $column instanceof TwigColumn && $column->getOption("passRow") ? $dataItem : $propertyAccess->getValue($dataItem, $propertyPath)
                );
            }

            $parseResult[] = $columnResult;
        }

        return $parseResult;
    }
}
