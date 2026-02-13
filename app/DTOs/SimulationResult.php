<?php

namespace App\DTOs;

class SimulationResult
{
    public function __construct(
        public readonly int $totalRows = 0,
        public readonly int $validRows = 0,
        public readonly int $skippedRows = 0,
        public readonly array $skippedByType = [],
        public readonly int $dynamicVideos = 0,
        public readonly int $staticVideos = 0,
        public readonly int $uniqueCombinations = 0,
        public readonly array $countPerOfferCode = [],
        public readonly int $emailCount = 0,
        public readonly int $smsCount = 0,
        public readonly int $attachmentCount = 0,
        public readonly int $missingSegmentsCount = 0,
        public readonly array $missingOfferCodes = [],
        public readonly array $filesProcessed = [],
    ) {}

    public function toArray(): array
    {
        return [
            'totalRows' => $this->totalRows,
            'validRows' => $this->validRows,
            'skippedRows' => $this->skippedRows,
            'skippedByType' => $this->skippedByType,
            'dynamicVideos' => $this->dynamicVideos,
            'staticVideos' => $this->staticVideos,
            'uniqueCombinations' => $this->uniqueCombinations,
            'countPerOfferCode' => $this->countPerOfferCode,
            'emailCount' => $this->emailCount,
            'smsCount' => $this->smsCount,
            'attachmentCount' => $this->attachmentCount,
            'missingSegmentsCount' => $this->missingSegmentsCount,
            'missingOfferCodes' => $this->missingOfferCodes,
            'filesProcessed' => $this->filesProcessed,
        ];
    }
}
