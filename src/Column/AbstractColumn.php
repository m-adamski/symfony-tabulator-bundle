<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractColumn {
    private array $options = [];

    public function __construct(array $options = []) {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public function getOptions(): array {
        return $this->options;
    }

    abstract protected function configureOptions(OptionsResolver $resolver): void;
}
