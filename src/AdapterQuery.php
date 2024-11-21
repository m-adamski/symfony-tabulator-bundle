<?php

namespace Adamski\Symfony\TabulatorBundle;

use Symfony\Component\HttpFoundation\InputBag;

class AdapterQuery {
    private bool $pagination = false;
    private ?int $paginationPage = null;
    private ?int $paginationSize = null;
    private ?InputBag $payload = null;

    public function isPagination(): bool {
        return $this->pagination;
    }

    public function setPagination(bool $pagination): AdapterQuery {
        $this->pagination = $pagination;
        return $this;
    }

    public function getPaginationPage(): ?int {
        return $this->paginationPage;
    }

    public function setPaginationPage(?int $paginationPage): AdapterQuery {
        $this->paginationPage = $paginationPage;
        return $this;
    }

    public function getPaginationSize(): ?int {
        return $this->paginationSize;
    }

    public function setPaginationSize(?int $paginationSize): AdapterQuery {
        $this->paginationSize = $paginationSize;
        return $this;
    }

    public function getPayload(): ?InputBag {
        return $this->payload;
    }

    public function setPayload(?InputBag $payload): AdapterQuery {
        $this->payload = $payload;
        return $this;
    }
}
