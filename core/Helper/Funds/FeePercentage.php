<?php

declare(strict_types=1);

namespace Trees\Helper\Funds;

/**
 * ========================================================
 * ********************************************************
 * ====== Enhanced Payment Fee Calculation Class ==========
 * ********************************************************
 * ========================================================
 *
 * This class calculates payment amounts considering:
 * - Third-party payment processor fees (percentage + flat)
 * - Website owner profit (percentage or flat)
 * - Tiered fee structure based on transaction amount
 */
class FeePercentage
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'local' => [
                'processor_percentage' => 1.5,  // Payment processor percentage fee
                'processor_tiers' => [          // Payment processor flat fees by amount tier
                    ['min' => 0,     'max' => 2000,  'fee' => 100],
                    ['min' => 2000,  'max' => 5000,  'fee' => 100],
                    ['min' => 5000,  'max' => 10000, 'fee' => 150],
                    ['min' => 10000, 'max' => 20000, 'fee' => 200],
                    ['min' => 20000, 'max' => 30000, 'fee' => 250],
                    ['min' => 30000, 'max' => null,  'fee' => 300],
                ],
                'owner_profit_percentage' => 0, // Default owner profit percentage
                'owner_profit_flat' => 0         // Default owner flat profit
            ],
            'international' => [
                'processor_percentage' => 3.9,
                'processor_tiers' => [
                    ['min' => 0,     'max' => 2000,  'fee' => 100],
                    ['min' => 2000,  'max' => 5000,  'fee' => 150],
                    ['min' => 5000,  'max' => 10000, 'fee' => 200],
                    ['min' => 10000, 'max' => null,  'fee' => 300],
                ],
                'owner_profit_percentage' => 0,
                'owner_profit_flat' => 0
            ]
        ], $config);

        // Sort tiers by min amount for both local and international
        foreach (['local', 'international'] as $type) {
            usort($this->config[$type]['processor_tiers'], function ($a, $b) {
                return $a['min'] <=> $b['min'];
            });
        }
    }

    /**
     * Set owner profit configuration
     *
     * @param float $percentage Profit percentage (e.g., 10 for 10%)
     * @param float $flat Flat profit amount
     * @param bool $isInternational Whether to set for international transactions
     */
    public function setOwnerProfit(float $percentage, float $flat, bool $isInternational = false): void
    {
        $type = $isInternational ? 'international' : 'local';
        $this->config[$type]['owner_profit_percentage'] = $percentage;
        $this->config[$type]['owner_profit_flat'] = $flat;
    }

    /**
     * Calculate the total amount customer should pay including all fees and profit
     *
     * @param float $baseAmount The base amount before any fees or profit
     * @param bool $isInternational Whether this is an international transaction
     * @return array Detailed breakdown of the calculation
     */
    public function calculateTotalAmount(float $baseAmount, bool $isInternational = false): array
    {
        $type = $isInternational ? 'international' : 'local';
        $settings = $this->config[$type];

        // First calculate amount after adding owner profit
        $ownerProfitAmount = $this->calculateOwnerProfit($baseAmount, $settings);
        $amountAfterProfit = $baseAmount + $ownerProfitAmount;

        // Then calculate payment processor fees
        $processorPercentage = $settings['processor_percentage'] / 100;
        $applicableTier = $this->findApplicableTier($amountAfterProfit, $processorPercentage, $settings['processor_tiers']);

        return $this->formatResult(
            $baseAmount,
            $ownerProfitAmount,
            $applicableTier['totalAmount'],
            $applicableTier['feeDetails'],
            $settings['processor_percentage'],
            $applicableTier['tier'],
            $settings['owner_profit_percentage'],
            $settings['owner_profit_flat']
        );
    }

    /**
     * Calculate owner profit based on configuration
     */
    private function calculateOwnerProfit(float $baseAmount, array $settings): float
    {
        $percentageProfit = $baseAmount * ($settings['owner_profit_percentage'] / 100);
        return $percentageProfit + $settings['owner_profit_flat'];
    }

    private function findApplicableTier(float $netAmount, float $percentage, array $tiers): array
    {
        foreach ($tiers as $tier) {
            // Calculate tentative total with this tier's fee
            $tentativeTotal = ($netAmount + $tier['fee']) / (1 - $percentage);

            // Check if within tier range
            $min = $tier['min'];
            $max = $tier['max'] ?? PHP_FLOAT_MAX;

            if ($tentativeTotal >= $min && $tentativeTotal <= $max) {
                $totalFee = $tentativeTotal * $percentage + $tier['fee'];
                return [
                    'totalAmount' => $tentativeTotal,
                    'feeDetails' => [
                        'percentageAmount' => $tentativeTotal * $percentage,
                        'flatFee' => $tier['fee'],
                        'totalFee' => $totalFee
                    ],
                    'tier' => $tier
                ];
            }
        }

        // Fallback to last tier if none found (shouldn't happen with proper configuration)
        $lastTier = end($tiers);
        $tentativeTotal = ($netAmount + $lastTier['fee']) / (1 - $percentage);
        $totalFee = $tentativeTotal * $percentage + $lastTier['fee'];

        return [
            'totalAmount' => $tentativeTotal,
            'feeDetails' => [
                'percentageAmount' => $tentativeTotal * $percentage,
                'flatFee' => $lastTier['fee'],
                'totalFee' => $totalFee
            ],
            'tier' => $lastTier
        ];
    }

    /**
     * Calculate net base amount after deducting all fees and profit
     *
     * @param float $totalAmount The total amount paid by customer
     * @param bool $isInternational Whether this is an international transaction
     * @return float The base amount after deducting all fees and profit
     */
    public function calculateNetBaseAmount(float $totalAmount, bool $isInternational = false): float
    {
        $type = $isInternational ? 'international' : 'local';
        $settings = $this->config[$type];

        // First deduct payment processor fees
        $processorPercentage = $settings['processor_percentage'] / 100;
        $processorFee = $this->getFeeForTotalAmount($totalAmount, $settings['processor_tiers']);
        $amountAfterProcessorFees = $totalAmount - ($totalAmount * $processorPercentage) - $processorFee;

        // Then deduct owner profit
        $ownerProfit = $this->reverseCalculateOwnerProfit($amountAfterProcessorFees, $settings);

        return round($amountAfterProcessorFees - $ownerProfit, 2);
    }

    /**
     * Reverse calculate owner profit from an amount that already includes it
     */
    private function reverseCalculateOwnerProfit(float $amountWithProfit, array $settings): float
    {
        if ($settings['owner_profit_percentage'] > 0) {
            return $amountWithProfit - ($amountWithProfit / (1 + ($settings['owner_profit_percentage'] / 100)));
        }
        return $settings['owner_profit_flat'];
    }

    private function getFeeForTotalAmount(float $totalAmount, array $tiers): float
    {
        foreach ($tiers as $tier) {
            $min = $tier['min'];
            $max = $tier['max'] ?? PHP_FLOAT_MAX;

            if ($totalAmount >= $min && $totalAmount <= $max) {
                return $tier['fee'];
            }
        }

        return end($tiers)['fee']; // Fallback to last tier
    }

    private function formatResult(
        float $baseAmount,
        float $ownerProfitAmount,
        float $totalAmount,
        array $feeDetails,
        float $processorPercentageRate,
        array $tier,
        float $ownerProfitPercentage,
        float $ownerProfitFlat
    ): array {
        return [
            'baseAmount' => round($baseAmount, 2),
            'totalAmount' => round($totalAmount, 2),
            'feeBreakdown' => [
                'payment_processor' => [
                    'percentage_rate' => $processorPercentageRate,
                    'percentage_amount' => round($feeDetails['percentageAmount'], 2),
                    'flat_fee' => round($feeDetails['flatFee'], 2),
                    'total_fee' => round($feeDetails['totalFee'], 2),
                ],
                'owner_profit' => [
                    'percentage_rate' => $ownerProfitPercentage,
                    'percentage_amount' => round($baseAmount * ($ownerProfitPercentage / 100), 2),
                    'flat_fee' => round($ownerProfitFlat, 2),
                    'total_profit' => round($ownerProfitAmount, 2)
                ]
            ],
            'appliedTier' => [
                'min' => $tier['min'],
                'max' => $tier['max'] ?? 'No limit',
                'fee' => $tier['fee']
            ],
            'merchantReceives' => round($baseAmount, 2),
            'ownerReceives' => round($ownerProfitAmount, 2),
            'customerPays' => round($totalAmount, 2),
            'totalFees' => round($feeDetails['totalFee'], 2)
        ];
    }

    /**
     * Calculate with profit margin including all fees
     *
     * @param float $baseAmount The base amount before any profit or fees
     * @param float $profitMargin The desired profit margin (percentage)
     * @param bool $isInternational Whether this is an international transaction
     * @return array Detailed breakdown of the calculation
     */
    public function calculateWithProfitMargin(float $baseAmount, float $profitMargin, bool $isInternational = false): array
    {
        $this->setOwnerProfit($profitMargin, 0, $isInternational);
        return $this->calculateTotalAmount($baseAmount, $isInternational);
    }

    /**
     * Calculate with flat profit including all fees
     *
     * @param float $baseAmount The base amount before any profit or fees
     * @param float $flatProfit The desired flat profit amount
     * @param bool $isInternational Whether this is an international transaction
     * @return array Detailed breakdown of the calculation
     */
    public function calculateWithFlatProfit(float $baseAmount, float $flatProfit, bool $isInternational = false): array
    {
        $this->setOwnerProfit(0, $flatProfit, $isInternational);
        return $this->calculateTotalAmount($baseAmount, $isInternational);
    }
}