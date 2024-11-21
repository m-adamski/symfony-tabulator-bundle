<?php

namespace Adamski\Symfony\TabulatorBundle;

use Adamski\Symfony\TabulatorBundle\Adapter\AbstractAdapter;
use Adamski\Symfony\TabulatorBundle\Column\AbstractColumn;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Tabulator {
    private array $options = [];
    private array $columns = [];
    private ?AbstractAdapter $adapter = null;

    public function __construct(
        private string   $selector,
        private ?Request $request = null,
    ) {}

    public function getSelector(): string {
        return $this->selector;
    }

    public function setSelector(string $selector): static {
        $this->selector = $selector;
        return $this;
    }

    public function getOptions(): array {
        return $this->options;
    }

    public function setOptions(array $options): static {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        return $this;
    }

    public function getColumns(): array {
        return $this->columns;
    }

    public function addColumn(string $name, string $columnClass, array $options = []): static {
        if (array_key_exists($name, $this->columns)) {
            throw new \InvalidArgumentException("Table already contains a column with name '$name'.");
        }

        $this->columns[$name] = new $columnClass($options);

        return $this;
    }

    public function getAdapter(): ?AbstractAdapter {
        return $this->adapter;
    }

    public function setAdapter(AbstractAdapter $adapter, array $options = []): static {
        $this->adapter = $adapter->setOptions($options);
        return $this;
    }

    public function createAdapter(string $adapterClass, array $options = []): static {
        $this->setAdapter(new $adapterClass, $options);
        return $this;
    }

    public function getRequest(): ?Request {
        return $this->request;
    }

    public function setRequest(?Request $request): static {
        $this->request = $request;
        return $this;
    }

    public function getConfig(): array {
        $tableOptions = $this->getOptions();

        if (!array_key_exists("ajaxURL", $tableOptions) || null === $tableOptions["ajaxURL"]) {
            throw new \InvalidArgumentException("The ajaxURL option must be set.");
        }

        return [
            "selector" => $this->getSelector(),
            "options"  => array_merge($tableOptions, [
                "columns" => array_map(function (AbstractColumn $column) {
                    return $column->getOptions();
                }, array_values($this->getColumns()))
            ]),
        ];
    }

    public function handleRequest(Request $request): ?JsonResponse {
        if (
            "tabulator" === $request->query->get("generator") ||
            "tabulator" === $request->headers->get("X-Request-Generator")
        ) {
            if (null === $this->getAdapter()) {
                throw new \InvalidArgumentException("Missing Adapter to handle request.");
            }

            // Call Adapter
            $adapterResult = $this->getAdapter()->getData(
                (new AdapterQuery())
                    ->setPagination($this->getOptions(true)["pagination"])
                    ->setPaginationSize($request->query->get("size") ?? $request->getPayload()->get("size"))
                    ->setPaginationPage($request->query->get("page") ?? $request->getPayload()->get("page"))
                    ->setPayload($request->getPayload())
            );

            return new JsonResponse(
                $this->getOptions()["pagination"] ? [
                    "data"      => $adapterResult->getData(),
                    "last_row"  => $adapterResult->getTotalRecords(),
                    "last_page" => $adapterResult->getTotalPages()
                ] : $adapterResult->getData()
            );
        }

        return null;
    }

    private function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            "ajaxURL"                => $this->request?->getRequestUri(),
            "ajaxConfig"             => Request::METHOD_POST,
            "ajaxContentType"        => "json",
            "ajaxParams"             => ["generator" => "tabulator"],
            "layout"                 => "fitColumns",
            "pagination"             => true,
            "paginationMode"         => "remote",
            "paginationSize"         => 25,
            "paginationButtonCount"  => 3,
            "paginationSizeSelector" => [10, 25, 50],
            "paginationCounter"      => "rows"
        ]);
    }
}
