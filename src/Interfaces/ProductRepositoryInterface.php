<?php

namespace App\Interfaces;

interface ProductRepositoryInterface
{
    public function getAll(
        int $limit,
        int $offset,
        ?string $search = null,
        ?float $minPrice = null,
        ?float $maxPrice = null,
        string $sort = 'created_at',
        string $order = 'desc'
    ): array;

    public function count(
        ?string $search = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): int;
}