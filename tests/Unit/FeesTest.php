<?php

namespace Tests\Unit;

use App\Support\Fees;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FeesTest extends TestCase
{
    /**
     * @return array<string, array{int, int}>
     */
    public static function serviceFeeCases(): array
    {
        return [
            'zero job' => [0, 0],
            'small job is 5%' => [10000, 500],
            'just under the cap' => [13800, 690],
            'exactly at the cap' => [13900, 695],
            'large job is capped' => [420000, 695],
        ];
    }

    #[DataProvider('serviceFeeCases')]
    public function test_service_fee_is_five_percent_capped_at_695(int $jobCents, int $expectedFee): void
    {
        $this->assertSame($expectedFee, Fees::serviceFee($jobCents));
    }

    public function test_processing_is_2_9_percent_plus_30_cents(): void
    {
        $this->assertSame(1335, Fees::processing(45000));
        $this->assertSame(0, Fees::processing(0));
    }

    public function test_customer_total_and_provider_payout(): void
    {
        $this->assertSame(45695, Fees::customerTotal(45000));
        $this->assertSame(43665, Fees::providerPayout(45000));
    }

    public function test_money_formatting(): void
    {
        $this->assertSame('$450', Fees::money(45000));
        $this->assertSame('$6.95', Fees::money(695));
        $this->assertSame('$450.00', Fees::money(45000, true));
    }
}
