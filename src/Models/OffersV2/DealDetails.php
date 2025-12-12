<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\OffersV2;

/**
 * DealDetails model for OffersV2
 * Specifies deal information of the offer
 */
class DealDetails
{
    private ?string $accessType = null;
    private ?string $badge = null;
    private ?int $earlyAccessDurationInMilliseconds = null;
    private ?string $endTime = null;
    private ?int $percentClaimed = null;
    private ?string $startTime = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->accessType = $data['AccessType'] ?? null;
            $this->badge = $data['Badge'] ?? null;
            $this->earlyAccessDurationInMilliseconds = isset($data['EarlyAccessDurationInMilliseconds']) 
                ? (int)$data['EarlyAccessDurationInMilliseconds'] 
                : null;
            $this->endTime = $data['EndTime'] ?? null;
            $this->percentClaimed = isset($data['PercentClaimed']) ? (int)$data['PercentClaimed'] : null;
            $this->startTime = $data['StartTime'] ?? null;
        }
    }

    /**
     * Get access type
     * Valid values: ALL, PRIME_EARLY_ACCESS, PRIME_EXCLUSIVE
     */
    public function getAccessType(): ?string
    {
        return $this->accessType;
    }

    /**
     * Get badge text (e.g., "Limited Time Deal", "With Prime", "Ends In")
     */
    public function getBadge(): ?string
    {
        return $this->badge;
    }

    /**
     * Get early access duration in milliseconds (for PRIME_EARLY_ACCESS deals)
     */
    public function getEarlyAccessDurationInMilliseconds(): ?int
    {
        return $this->earlyAccessDurationInMilliseconds;
    }

    /**
     * Get deal end time (UTC)
     */
    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    /**
     * Get percentage of deal capacity claimed
     */
    public function getPercentClaimed(): ?int
    {
        return $this->percentClaimed;
    }

    /**
     * Get deal start time (UTC)
     */
    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'AccessType' => $this->accessType,
            'Badge' => $this->badge,
            'EarlyAccessDurationInMilliseconds' => $this->earlyAccessDurationInMilliseconds,
            'EndTime' => $this->endTime,
            'PercentClaimed' => $this->percentClaimed,
            'StartTime' => $this->startTime,
        ], fn($value) => $value !== null);
    }
}
