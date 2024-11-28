<?php

namespace Adamski\Symfony\TabulatorBundle\Filter;

enum FilteringComparison: string {
    case AND = "and";
    case OR = "or";
}
