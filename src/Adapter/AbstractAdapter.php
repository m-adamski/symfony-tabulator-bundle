<?php

namespace Adamski\Symfony\TabulatorBundle\Adapter;

use Adamski\Symfony\TabulatorBundle\AdapterQuery;
use Adamski\Symfony\TabulatorBundle\ResultInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractAdapter {
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

    public function setOptions(array $options): static {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        return $this;
    }

    public abstract function getData(AdapterQuery $adapterQuery): ResultInterface;

    protected abstract function configureOptions(OptionsResolver $resolver): void;
}
