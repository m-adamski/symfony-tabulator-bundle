<?php

namespace Adamski\Symfony\TabulatorBundle;

use Adamski\Symfony\TabulatorBundle\DependencyInjection\Compiler\LocatorRegistrationPass;
use Adamski\Symfony\TabulatorBundle\DependencyInjection\TabulatorExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TabulatorBundle extends Bundle {
    public function build(ContainerBuilder $container): void {
        parent::build($container);

        // Compiler Pass
        // https://symfony.com/doc/current/service_container/compiler_passes.html#working-with-compiler-passes-in-bundles
        $container->addCompilerPass(new LocatorRegistrationPass());
    }

    public function getContainerExtension(): ?ExtensionInterface {
        return new TabulatorExtension();
    }
}
