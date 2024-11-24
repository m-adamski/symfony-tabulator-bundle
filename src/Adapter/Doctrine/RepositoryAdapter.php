<?php

namespace Adamski\Symfony\TabulatorBundle\Adapter\Doctrine;

use Adamski\Symfony\TabulatorBundle\Adapter\AbstractAdapter;
use Adamski\Symfony\TabulatorBundle\AdapterQuery;
use Adamski\Symfony\TabulatorBundle\ArrayResult;
use Adamski\Symfony\TabulatorBundle\ResultInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepositoryAdapter extends AbstractAdapter {
    private ?ManagerRegistry $managerRegistry = null;

    public function __construct(?ManagerRegistry $managerRegistry = null) {
        if (null === $managerRegistry) {
            throw new \InvalidArgumentException("Install doctrine/doctrine-bundle to use the ORMAdapter");
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

        // Process sorting
        if ($adapterQuery->getSortingBag()->hasSorting()) {
            foreach ($adapterQuery->getSortingBag()->getSortingItems() as $sortingItem) {
                $queryBuilder->addOrderBy(
                    $rootAlias . "." . $sortingItem->getColumn()->getOption("field"),
                    $sortingItem->getDirection()->value
                );
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
}
