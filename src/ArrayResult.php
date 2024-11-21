<?php

namespace Adamski\Symfony\TabulatorBundle;

class ArrayResult implements ResultInterface {
    public function __construct(
        private readonly array $data,
        private readonly ?int  $totalPages = null,
        private readonly ?int  $totalRecords = null,
    ) {}

    public function getData(): array {
        return $this->data;
    }

    public function getTotalPages(): ?int {
        return $this->totalPages;
    }

    public function getTotalRecords(): ?int {
        return $this->totalRecords;
    }
}
