<?php

namespace App\Data;

final readonly class KinshipResult
{
    public function __construct(
        public string $arabicLabel,
        public array  $arabicLabels,        // all descriptions: [from A, from B, ...]
        public bool   $relationshipFound,
    ) {}
}
