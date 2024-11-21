<?php

namespace Adamski\Symfony\TabulatorBundle;

interface ResultInterface {
    public function getTotalPages(): ?int;

    public function getTotalRecords(): ?int;

    public function getData(): array;
}
