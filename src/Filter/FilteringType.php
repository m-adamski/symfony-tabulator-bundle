<?php

namespace Adamski\Symfony\TabulatorBundle\Filter;

enum FilteringType: string {
    case EQUAL = "=";
    case NOT_EQUAL = "!=";
    case LIKE = "like";
    case STARTS_WITH = "starts";
    case ENDS_WITH = "ends";
    case LESS = "<";
    case LESS_OR_EQUAL = "<=";
    case GREATER = ">";
    case GREATER_OR_EQUAL = ">=";
    case IN = "in";
    case REGEX = "regex";
    case KEYWORDS = "keywords";
}
