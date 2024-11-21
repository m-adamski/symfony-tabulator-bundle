<?php

namespace Adamski\Symfony\TabulatorBundle;

use Symfony\Component\HttpFoundation\RequestStack;

class TabulatorFactory {
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {}

    public function create(string $selector): Tabulator {
        return new Tabulator($selector, $this->requestStack->getCurrentRequest());
    }
}
