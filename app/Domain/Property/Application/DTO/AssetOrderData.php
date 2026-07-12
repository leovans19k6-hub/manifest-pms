<?php

namespace Domain\Property\Application\DTO;

final readonly class AssetOrderData
{
    /** @param list<string> $assetIds */
    public function __construct(public array $assetIds) {}
}
