<?php

namespace Adamski\Symfony\TabulatorBundle\Twig;

use Adamski\Symfony\TabulatorBundle\Tabulator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TabulatorExtension extends AbstractExtension {
    public function getFunctions(): array {
        return [
            new TwigFunction("tabulator_config", [$this, "config"]),
        ];
    }

    public function config(Tabulator $tabulator): string {
        return json_encode($tabulator->getConfig(), JSON_THROW_ON_ERROR);
    }
}
