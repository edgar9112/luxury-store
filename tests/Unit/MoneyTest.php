<?php

namespace Tests\Unit;

use App\Support\Money;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_formats_amounts_in_usd(): void
    {
        $this->assertSame('$4,850.00', Money::format(485000));
        $this->assertSame('$349.00', Money::format(34900));
        $this->assertSame('$4.95', Money::format(495));
        $this->assertSame('$1,234,567.89', Money::format(123456789));
    }

    public function test_it_keeps_euro_formatting_when_requested(): void
    {
        $this->assertSame('4.850,00 €', Money::format(485000, 'EUR'));
    }
}
