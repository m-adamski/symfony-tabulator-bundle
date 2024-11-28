<?php

namespace Adamski\Symfony\TabulatorBundleTests;

use Adamski\Symfony\TabulatorBundle\DependencyInjection\InstanceStorage;
use Adamski\Symfony\TabulatorBundle\Parser\ParserInterface;
use Adamski\Symfony\TabulatorBundle\Parser\PropertyParser;
use Adamski\Symfony\TabulatorBundle\Tabulator;
use Adamski\Symfony\TabulatorBundle\TabulatorFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TabulatorFactoryTest extends TestCase {
    private readonly RequestStack $requestStack;
    private readonly InstanceStorage $instanceStorage;
    private readonly ParserInterface $parser;

    protected function setUp(): void {

        $request = new Request();
        $request->server->set("REQUEST_URI", "/");

        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->method("getCurrentRequest")
            ->willReturn($request);

        $this->instanceStorage = new InstanceStorage();
        $this->parser = new PropertyParser();
    }

    /**
     * @covers ::create
     */
    public function testCreate() {
        $factory = new TabulatorFactory(
            $this->requestStack,
            $this->instanceStorage,
            $this->parser,
        );

        $tabulator = $factory->create("#tabulator");

        $this->assertInstanceOf(Tabulator::class, $tabulator);
    }
}
