<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class TwigColumn extends AbstractColumn {
    private ?Environment $environment = null;

    public function __construct(?Environment $environment = null) {
        if (null === $environment) {
            throw new \InvalidArgumentException("Install symfony/twig-bundle to use the TwigColumn");
        }

        $this->environment = $environment;
    }

    public function prepareContent($value): mixed {
        return $this->environment->render($this->getOption("template"), [
            "content" => $value
        ]);
    }

    protected function getDefaultConfig(): array {
        return ["formatter" => "html"];
    }

    protected function configureOptions(OptionsResolver $resolver): void {
        parent::configureOptions($resolver);

        $resolver->setRequired(["template"])
            ->setDefaults(["passRow" => false])
            ->setAllowedTypes("template", "string")
            ->setAllowedTypes("passRow", "bool");
    }
}
