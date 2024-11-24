<?php

namespace Adamski\Symfony\TabulatorBundle\DependencyInjection\Compiler;

use Adamski\Symfony\TabulatorBundle\Adapter\AbstractAdapter;
use Adamski\Symfony\TabulatorBundle\Column\AbstractColumn;
use Adamski\Symfony\TabulatorBundle\DependencyInjection\InstanceStorage;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

class LocatorRegistrationPass implements CompilerPassInterface {
    public function process(ContainerBuilder $container): void {
        $container->getDefinition(InstanceStorage::class)
            ->setArguments([
                [
                    AbstractAdapter::class => $this->registerLocator($container, "adapter"),
                    AbstractColumn::class  => $this->registerLocator($container, "column"),
                ]
            ]);
    }

    private function registerLocator(ContainerBuilder $container, string $baseTag): Definition {
        $types = [];
        foreach ($container->findTaggedServiceIds("tabulator.{$baseTag}") as $serviceId => $tag) {
            $types[$serviceId] = new Reference($serviceId);
        }

        return $container
            ->register("tabulator.{$baseTag}_locator", ServiceLocator::class)
            ->addTag("container.service_locator")
            ->setPublic(false)
            ->setArguments([$types]);
    }
}
