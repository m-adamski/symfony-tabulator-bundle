<?php

namespace Adamski\Symfony\TabulatorBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TabulatorExtension extends AbstractExtension {
    public function getFunctions(): array {
        return [
            new TwigFunction("tabulator_config", [$this, "config"]),
        ];
    }

    public function config(array $config): string {
        return json_encode($config, JSON_THROW_ON_ERROR);
    }
}
