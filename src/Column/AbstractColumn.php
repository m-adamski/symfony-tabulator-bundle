<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractColumn {
    private array $options = [];

    public function getOptions(): array {
        return $this->options;
    }

    public function getOption(string $name): mixed {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException("Option '$name' does not exist");
        }

        return $this->options[$name];
    }

    public function setOptions(array $options): AbstractColumn {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);

        return $this;
    }

    public function getConfig(): array {
        return array_merge_recursive([
            "title"   => $this->getOption("title"),
            "field"   => $this->getOption("field"),
            "visible" => $this->getOption("visible"),
        ], $this->getDefaultConfig(), $this->getOption("extra"));
    }

    protected function getDefaultConfig(): array {
        return [];
    }

    protected function configureOptions(OptionsResolver $resolver): void {
        $resolver
            ->setRequired(["title", "field"])
            ->setDefaults([
                "visible"    => true,
                "filterable" => true,
                "extra"      => []
            ])
            ->setAllowedTypes("title", "string")
            ->setAllowedTypes("field", "string")
            ->setAllowedTypes("visible", "bool")
            ->setAllowedTypes("filterable", "bool")
            ->setAllowedTypes("extra", "array");
    }

    abstract public function prepareContent($value): mixed;
}
