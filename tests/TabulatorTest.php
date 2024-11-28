<?php

namespace Adamski\Symfony\TabulatorBundleTests;

use Adamski\Symfony\TabulatorBundle\Adapter\ArrayAdapter;
use Adamski\Symfony\TabulatorBundle\Column\DateTimeColumn;
use Adamski\Symfony\TabulatorBundle\Column\TextColumn;
use Adamski\Symfony\TabulatorBundle\DependencyInjection\InstanceStorage;
use Adamski\Symfony\TabulatorBundle\Parser\PropertyParser;
use Adamski\Symfony\TabulatorBundle\Tabulator;
use Adamski\Symfony\TabulatorBundle\TabulatorFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TabulatorTest extends TestCase {
    private Tabulator $tabulator;

    protected function setUp(): void {
        $request = new Request();
        $request->server->set("REQUEST_URI", "/tabulator");

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method("getCurrentRequest")
            ->willReturn($request);

        $instanceStorage = new InstanceStorage();
        $propertyParser = new PropertyParser();

        $tabulatorFactory = new TabulatorFactory($requestStack, $instanceStorage, $propertyParser);

        // Create instance of the Tabulator
        $this->tabulator = $tabulatorFactory->create("#tabulator")
            ->setOptions([
                "ajaxConfig"      => "POST",
                "ajaxContentType" => "json",
            ])
            ->addColumn("name", TextColumn::class, [
                "title" => "Name",
            ]);
    }

    /**
     * @covers ::getSelector
     */
    public function testGetSelector(): void {
        $this->assertEquals("#tabulator", $this->tabulator->getSelector());
    }

    /**
     * @covers ::setSelector
     */
    public function testSetSelector(): void {
        $tabulator = $this->tabulator->setSelector("#table");

        $this->assertInstanceOf(Tabulator::class, $tabulator);
        $this->assertEquals("#table", $this->tabulator->getSelector());
    }

    /**
     * @covers ::getOptions
     */
    public function testGetOptions(): void {
        $options = $this->tabulator->getOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey("ajaxURL", $options);
        $this->assertEquals("/tabulator", $options["ajaxURL"]);
    }

    /**
     * @covers ::getOption
     */
    public function testGetOption(): void {
        $this->assertEquals("/tabulator", $this->tabulator->getOption("ajaxURL"));
    }

    /**
     * @covers ::setOptions
     */
    public function testSetOptions(): void {
        $tabulator = $this->tabulator->setOptions(["ajaxURL" => "/table"]);

        $this->assertInstanceOf(Tabulator::class, $tabulator);
        $this->assertEquals("/table", $this->tabulator->getOptions()["ajaxURL"]);
    }

    /**
     * @covers ::getColumns
     */
    public function testGetColumns(): void {
        $columns = $this->tabulator->getColumns();

        $this->assertIsArray($columns);
        $this->assertArrayHasKey("name", $columns);
        $this->assertCount(1, $columns);
    }

    /**
     * @covers ::getColumn
     */
    public function testGetColumn(): void {
        $column = $this->tabulator->getColumn("name");

        $this->assertInstanceOf(TextColumn::class, $column);
    }

    /**
     * @covers ::getColumn
     */
    public function testGetColumnException(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->tabulator->getColumn("example");
    }

    /**
     * @covers ::addColumn
     */
    public function testAddColumn(): void {
        $this->tabulator->addColumn("example", TextColumn::class, [
            "title" => "Example",
        ]);

        $this->assertCount(2, $this->tabulator->getColumns());
    }

    /**
     * @covers ::addColumn
     */
    public function testAddColumnException(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->tabulator->addColumn("name", TextColumn::class, [
            "title" => "Name",
        ]);
    }

    /**
     * @covers ::getAdapter
     */
    public function testGetAdapter(): void {
        $this->assertNull($this->tabulator->getAdapter());
    }

    /**
     * @covers ::setAdapter
     */
    public function testSetAdapter(): void {
        $tabulator = $this->tabulator->setAdapter(new ArrayAdapter(), ["data" => []]);

        $this->assertInstanceOf(Tabulator::class, $tabulator);
        $this->assertInstanceOf(ArrayAdapter::class, $tabulator->getAdapter());
    }

    /**
     * @covers ::createAdapter
     */
    public function testCreateAdapter(): void {
        $tabulator = $this->tabulator->createAdapter(ArrayAdapter::class, ["data" => []]);

        $this->assertInstanceOf(Tabulator::class, $tabulator);
        $this->assertInstanceOf(ArrayAdapter::class, $tabulator->getAdapter());
    }

    /**
     * @covers ::getConfig
     */
    public function testGetConfig(): void {
        $config = $this->tabulator->getConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey("selector", $config);
        $this->assertArrayHasKey("options", $config);
    }

    /**
     * @covers ::handleRequest
     */
    public function testHandleRequest(): void {
        $this->updateTabulator();

        $request = $this->createRequest();
        $response = $this->tabulator->handleRequest($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey("data", $responseData);
        $this->assertCount(5, $responseData["data"]);
    }

    /**
     * @covers ::handleRequest
     */
    public function testHandleRequestWithSort(): void {
        $this->updateTabulator();

        $request = $this->createRequest();
        $request->request->set("sort", [["field" => "name", "dir" => "desc"]]);

        $response = $this->tabulator->handleRequest($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey("data", $responseData);
        $this->assertCount(5, $responseData["data"]);

        $this->assertEquals("Test5", $responseData["data"][0]["name"]);
    }

    /**
     * @covers ::handleRequest
     */
    public function testHandleRequestWithFilter(): void {
        $this->updateTabulator();

        $request = $this->createRequest();
        $request->request->set("filter", [[["field" => "name", "type" => "=", "value" => "Test5"]]]);

        $response = $this->tabulator->handleRequest($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey("data", $responseData);
        $this->assertCount(1, $responseData["data"]);

        $this->assertEquals("Test5", $responseData["data"][0]["name"]);
    }

    private function updateTabulator(): void {
        $this->tabulator
            ->addColumn("secretToken", TextColumn::class, ["title" => "Secret"])
            ->addColumn("active", TextColumn::class, ["title" => "Status"])
            ->addColumn("creationDate", DateTimeColumn::class, ["title" => "Status", "format" => "Y-m-d"])
            ->createAdapter(ArrayAdapter::class, [
                "data" => [
                    ["id" => 1, "name" => "Test1", "secretToken" => "A", "active" => true, "creationDate" => new \DateTime()],
                    ["id" => 2, "name" => "Test2", "secretToken" => "B", "active" => true, "creationDate" => new \DateTime()],
                    ["id" => 3, "name" => "Test3", "secretToken" => "C", "active" => true, "creationDate" => new \DateTime()],
                    ["id" => 4, "name" => "Test4", "secretToken" => "D", "active" => true, "creationDate" => new \DateTime()],
                    ["id" => 5, "name" => "Test5", "secretToken" => "E", "active" => true, "creationDate" => new \DateTime()],
                ]
            ]);
    }

    private function createRequest(): Request {
        $request = new Request();
        $request->server->set("REQUEST_URI", "/tabulator");
        $request->server->set("REQUEST_METHOD", "POST");
        $request->headers->set("X-Request-Generator", "tabulator");
        $request->request->set("size", 25);
        $request->request->set("page", 1);

        return $request;
    }
}
