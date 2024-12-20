<?php

namespace Adamski\Symfony\TabulatorBundle;

use Adamski\Symfony\TabulatorBundle\DependencyInjection\InstanceStorage;
use Adamski\Symfony\TabulatorBundle\Parser\ParserInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class TabulatorFactory {
    public function __construct(
        private readonly RequestStack    $requestStack,
        private readonly InstanceStorage $instanceStorage,
        private readonly ParserInterface $parser,
    ) {}

    public function create(string $selector, array $options = []): Tabulator {
        return (new Tabulator($selector, $this->requestStack->getCurrentRequest(), $this->instanceStorage, $this->parser))
            ->setOptions($options);
    }
}
