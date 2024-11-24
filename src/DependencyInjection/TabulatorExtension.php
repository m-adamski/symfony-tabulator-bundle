<?php

namespace Adamski\Symfony\TabulatorBundle\DependencyInjection;

use Adamski\Symfony\TabulatorBundle\Adapter\AbstractAdapter;
use Adamski\Symfony\TabulatorBundle\Column\AbstractColumn;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class TabulatorExtension extends Extension {
    public function load(array $configs, ContainerBuilder $container) {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $loader->load("services.yaml");

        $container->registerForAutoconfiguration(AbstractAdapter::class)
            ->addTag("tabulator.adapter")
            ->setShared(false);

        $container->registerForAutoconfiguration(AbstractColumn::class)
            ->addTag("tabulator.column")
            ->setShared(false);
    }
}
