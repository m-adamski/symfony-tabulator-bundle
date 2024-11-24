<?php

namespace Adamski\Symfony\TabulatorBundle\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;

class TickCrossColumn extends AbstractColumn {
    public function prepareContent($value): mixed {
        return $value;
    }

    protected function getDefaultConfig(): array {
        return [
            "formatter"       => "tickCross",
            "formatterParams" => [
                "tickElement"  => $this->getOption("tickElement"),
                "crossElement" => $this->getOption("crossElement"),
            ],
        ];
    }

    protected function configureOptions(OptionsResolver $resolver): void {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(["tickElement", "crossElement"])
            ->setDefaults([
                "extra" => [
                    "formatterParams" => [
                        "allowEmpty"  => false,
                        "allowTruthy" => false,
                    ]
                ]
            ])
            ->setAllowedTypes("tickElement", "string")
            ->setAllowedTypes("crossElement", "string");
    }
}
