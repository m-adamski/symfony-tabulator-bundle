<?php

namespace Adamski\Symfony\TabulatorBundle;

use Adamski\Symfony\TabulatorBundle\Adapter\AbstractAdapter;
use Adamski\Symfony\TabulatorBundle\Column\AbstractColumn;
use Adamski\Symfony\TabulatorBundle\DependencyInjection\InstanceStorage;
use Adamski\Symfony\TabulatorBundle\Filter\FilteringComparison;
use Adamski\Symfony\TabulatorBundle\Filter\FilteringItem;
use Adamski\Symfony\TabulatorBundle\Filter\FilteringType;
use Adamski\Symfony\TabulatorBundle\Parser\ParserInterface;
use Adamski\Symfony\TabulatorBundle\Sorter\SortingDirection;
use Adamski\Symfony\TabulatorBundle\Sorter\SortingItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Tabulator {
    private array $options = [];
    private array $columns = [];
    private ?AbstractAdapter $adapter = null;

    public function __construct(
        private string                   $selector,
        private readonly ?Request        $request,
        private readonly InstanceStorage $instanceStorage,
        private readonly ParserInterface $parser,
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

    public function getOption(string $name): mixed {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException("Option '$name' does not exist");
        }

        return $this->options[$name];
    }

    public function setOptions(array $options): static {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        return $this;
    }

    public function getColumns(): array {
//        $identifierColumns = array_filter($this->columns, function (AbstractColumn $column) {
//            return "id" === $column->getOption("field");
//        });
//
//        // Check if ID column is already set
//        if (count($identifierColumns) <= 0) {
//            $this->addColumn("id", HiddenColumn::class, [
//                "field" => "id",
//                "title" => "#",
//            ]);
//        }

        return $this->columns;
    }

    public function getColumn(string $name): AbstractColumn {
        if (!array_key_exists($name, $this->columns)) {
            throw new \InvalidArgumentException("Column '$name' does not exist");
        }

        return $this->columns[$name];
    }

    public function addColumn(string $name, string $columnClass, array $options = []): static {
        if (array_key_exists($name, $this->columns)) {
            throw new \InvalidArgumentException("Table already contains a column with name '$name'");
        }

        $this->columns[$name] = $this->instanceStorage->getColumn($columnClass)
            ->setOptions(array_merge(["field" => $name], $options));

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
        $this->setAdapter($this->instanceStorage->getAdapter($adapterClass), $options);
        return $this;
    }

    /**
     * Generate configuration which will be provided to the JS library.
     *
     * @return array
     */
    public function getConfig(): array {
        $tableOptions = $this->getOptions();

        if (!array_key_exists("ajaxURL", $tableOptions) || empty($tableOptions["ajaxURL"])) {
            throw new \InvalidArgumentException("The ajaxURL option must be set");
        }

        return [
            "selector" => $this->getSelector(),
            "options"  => array_merge($tableOptions, [
                "columns" => array_map(function (AbstractColumn $column) {
                    return $column->getConfig();
                }, array_values($this->getColumns()))
            ]),
        ];
    }

    /**
     * Handle Request.
     *
     * @param Request $request
     * @return JsonResponse|null
     */
    public function handleRequest(Request $request): ?JsonResponse {
        if (
            "tabulator" === $request->query->get("generator") ||
            "tabulator" === $request->headers->get("X-Request-Generator")
        ) {
            if (null === $this->getAdapter()) {
                throw new \InvalidArgumentException("Missing Adapter to handle request");
            }

            // Generate AdapterQuery
            $adapterQuery = (new AdapterQuery())
                ->setPagination($this->getOption("pagination"))
                ->setPaginationSize($request->query->get("size") ?? $request->getPayload()->get("size"))
                ->setPaginationPage($request->query->get("page") ?? $request->getPayload()->get("page"))
                ->setPayload($request->getPayload());

            // Process sorting
            $requestSort = !empty($request->query->all("sort")) ? $request->query->all("sort") : $request->getPayload()->all("sort");

            if (count($requestSort) > 0) {
                foreach ($requestSort as $value) {
                    $adapterQuery->getSortingBag()->addSortingItem(
                        (new SortingItem())
                            ->setColumn($this->getColumn($value["field"]))
                            ->setDirection(SortingDirection::from($value["dir"]))
                    );
                }
            }

            // Process filtering
            $requestFilter = !empty($request->query->all("filter")) ? $request->query->all("filter") : $request->getPayload()->all("filter");

            if (count($requestFilter) > 0) {
                foreach ($requestFilter as $value) {
                    if (isset($value["value"])) {
                        $filterColumn = $this->getColumn($value["field"]);
                        $filterColumnName = $filterColumn->getOption("field");

                        if (false === $filterColumn->getOption("filterable")) {
                            throw new \InvalidArgumentException("Filtering by the '$filterColumnName' column is disabled");
                        }

                        $adapterQuery->getFilteringBag()->addFilter(
                            (new FilteringItem())
                                ->setColumn($filterColumn)
                                ->setType(FilteringType::from($value["type"]))
                                ->setValue($value["value"]), FilteringComparison::AND
                        );
                    } else {
                        foreach ($value as $item) {
                            $filterColumn = $this->getColumn($item["field"]);
                            $filterColumnName = $filterColumn->getOption("field");

                            if (false === $filterColumn->getOption("filterable")) {
                                throw new \InvalidArgumentException("Filtering by the '$filterColumnName' column is disabled");
                            }

                            $adapterQuery->getFilteringBag()->addFilter(
                                (new FilteringItem())
                                    ->setColumn($filterColumn)
                                    ->setType(FilteringType::from($item["type"]))
                                    ->setValue($item["value"]), FilteringComparison::OR
                            );
                        }
                    }
                }
            }

            // Call Adapter
            $adapterResult = $this->getAdapter()->getData($adapterQuery);

            // Parse data
            $adapterData = $this->parser->parse($adapterResult->getData(), $this->getColumns());

            return new JsonResponse(
                $this->getOption("pagination") ? [
                    "data"      => $adapterData,
                    "last_row"  => $adapterResult->getTotalRecords(),
                    "last_page" => $adapterResult->getTotalPages()
                ] : $adapterData
            );
        }

        return null;
    }

    /**
     * Configure Options.
     *
     * @param OptionsResolver $resolver
     * @return void
     */
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
            "paginationCounter"      => "rows",
            "filterMode"             => "remote",
            "sortMode"               => "remote",
            "placeholder"            => false,
        ])->setIgnoreUndefined();
    }
}
