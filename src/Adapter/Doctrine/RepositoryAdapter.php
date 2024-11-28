<?php

namespace Adamski\Symfony\TabulatorBundle\Adapter\Doctrine;

use Adamski\Symfony\TabulatorBundle\Adapter\AbstractAdapter;
use Adamski\Symfony\TabulatorBundle\AdapterQuery;
use Adamski\Symfony\TabulatorBundle\ArrayResult;
use Adamski\Symfony\TabulatorBundle\Filter\FilteringComparison;
use Adamski\Symfony\TabulatorBundle\Filter\FilteringType;
use Adamski\Symfony\TabulatorBundle\ResultInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepositoryAdapter extends AbstractAdapter {
    private ?ManagerRegistry $managerRegistry = null;

    public function __construct(?ManagerRegistry $managerRegistry = null) {
        if (null === $managerRegistry) {
            throw new \InvalidArgumentException("Install doctrine/doctrine-bundle to use the RepositoryAdapter");
        }

        $this->managerRegistry = $managerRegistry;
    }

    public function getData(AdapterQuery $adapterQuery): ResultInterface {
        $repository = $this->managerRegistry
            ?->getManagerForClass($this->getOption("entity"))
            ?->getRepository($this->getOption("entity"));

        if (null === $repository) {
            throw new \RuntimeException("Repository for entity '{$this->getOption("entity")}' not found");
        }

        // Get QueryBuilder and Root Alias
        $queryBuilder = $this->getOption("query_builder")($repository);
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if (!$queryBuilder instanceof QueryBuilder) {
            throw new \InvalidArgumentException("The function must return an instance of QueryBuilder");
        }

        // Process filtering
        if ($adapterQuery->getFilteringBag()->hasFiltering()) {
            $andExpression = $queryBuilder->expr()->andX();
            $orExpression = $queryBuilder->expr()->orX();

            foreach ($adapterQuery->getFilteringBag()->getFilters() as $comparison => $filterItems) {
                foreach ($filterItems as $filterItem) {
                    $queryExpression = $this->getQueryExpression($filterItem->getType());
                    $queryFieldName = sprintf("%s.%s", $rootAlias, $filterItem->getColumn()->getOption("field"));
                    $queryFilterParameter = uniqid("_param_");

                    if (null === $queryExpression) {
                        throw new \InvalidArgumentException("Provided filtering type is not supported by the RepositoryAdapter");
                    }

                    // Add Query with Expression (value as parameter with generated random name)
                    ${$comparison === FilteringComparison::OR->value ? "orExpression" : "andExpression"}->add(
                        $queryBuilder->expr()->{$queryExpression}($queryFieldName, sprintf(":%s", $queryFilterParameter))
                    );

                    $queryBuilder->setParameter($queryFilterParameter, $this->prepareQueryParameter($filterItem->getType(), $filterItem->getValue()));
                }
            }

            // Add generated Expression Queries
            // https://tabulator.info/docs/6.3/filter#func-complex
            $queryBuilder->andWhere($andExpression)->andWhere($orExpression);
        }

        // Process sorting
        if ($adapterQuery->getSortingBag()->hasSorting()) {
            foreach ($adapterQuery->getSortingBag()->getSortingItems() as $sortingItem) {
                $queryFieldName = sprintf("%s.%s", $rootAlias, $sortingItem->getColumn()->getOption("field"));

                $queryBuilder->addOrderBy($queryFieldName, $sortingItem->getDirection()->value);
            }
        }

        // Process pagination
        if ($adapterQuery->isPagination()) {
            $queryBuilder
                ->setFirstResult($adapterQuery->getPaginationSize() * $adapterQuery->getPaginationPage() - $adapterQuery->getPaginationSize())
                ->setMaxResults($adapterQuery->getPaginationSize());

            $queryPaginator = new Paginator($queryBuilder);
            $queryResult = $queryBuilder->getQuery()->getResult();

            return new ArrayResult($queryResult, ceil($queryPaginator->count() / $adapterQuery->getPaginationSize()), $queryPaginator->count());
        }

        return new ArrayResult($queryResult);
    }

    protected function configureOptions(OptionsResolver $resolver): void {
        $resolver->setRequired(["entity", "query_builder"])
            ->setAllowedTypes("entity", "string")
            ->setAllowedTypes("query_builder", "callable");
    }

    /**
     * Parse FilteringType to QueryBuilder expression.
     *
     * @param FilteringType $type
     * @return string|null
     */
    private function getQueryExpression(FilteringType $type): ?string {
        return match ($type) {
            FilteringType::EQUAL            => "eq",
            FilteringType::NOT_EQUAL        => "neq",
            FilteringType::LIKE             => "like",
            FilteringType::LESS             => "lt",
            FilteringType::LESS_OR_EQUAL    => "lte",
            FilteringType::GREATER          => "gt",
            FilteringType::GREATER_OR_EQUAL => "gte",
            default                         => null
        };
    }

    /**
     * Prepare value for Query Parameter.
     *
     * @param FilteringType $type
     * @param mixed         $value
     * @return mixed
     */
    private function prepareQueryParameter(FilteringType $type, mixed $value): mixed {
        return match ($type) {
            FilteringType::LIKE => sprintf("%%%s%%", $value),
            default             => $value
        };
    }
}
