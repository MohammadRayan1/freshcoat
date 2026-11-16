<?php

namespace App\Support;

/**
 * FreshCoat's pricing rules, all in cents.
 */
class Fees
{
    public const SERVICE_FEE_RATE = 0.05;

    public const SERVICE_FEE_CAP_CENTS = 695;

    public const PROCESSING_RATE = 0.029;

    public const PROCESSING_FIXED_CENTS = 30;

    public const PRO_MONTHLY_CENTS = 795;

    public const PRO_TRIAL_MONTHS = 6;

    /**
     * Customer service fee: 5% of the job price, capped at $6.95.
     */
    public static function serviceFee(int $jobCents): int
    {
        if ($jobCents <= 0) {
            return 0;
        }

        return min((int) round($jobCents * self::SERVICE_FEE_RATE), self::SERVICE_FEE_CAP_CENTS);
    }

    /**
     * Estimated card processing on a charge: 2.9% + 30¢.
     */
    public static function processing(int $chargeCents): int
    {
        if ($chargeCents <= 0) {
            return 0;
        }

        return (int) round($chargeCents * self::PROCESSING_RATE) + self::PROCESSING_FIXED_CENTS;
    }

    public static function customerTotal(int $jobCents): int
    {
        return $jobCents + self::serviceFee($jobCents);
    }

    public static function providerPayout(int $jobCents): int
    {
        return max(0, $jobCents - self::processing($jobCents));
    }

    public static function money(int $cents, bool $alwaysShowCents = false): string
    {
        $decimals = ($alwaysShowCents || $cents % 100 !== 0) ? 2 : 0;

        return '$'.number_format($cents / 100, $decimals);
    }
}
