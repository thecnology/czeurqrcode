<?php

declare(strict_types=1);

namespace thecnology\czeurqrcode\Tests;

use PHPUnit\Framework\TestCase;
use thecnology\czeurqrcode\PaymentRequest;

class PaymentRequestTest extends TestCase
{
    public function testCzkAccountReturnsSpaydPayload(): void
    {
        $request = new PaymentRequest(
            amount: 100.0,
            currency: PaymentRequest::CURRENCY_CZK,
            message: 'Test',
            accountNumber: '1234567890',
            bankCode: '0800',
            variableSymbol: '123456',
        );

        $data = $request->getQrCodeData();

        $this->assertStringStartsWith('SPD*', $data);
        $this->assertStringContainsString('AM:100', $data);
        $this->assertStringContainsString('CC:CZK', $data);
        $this->assertStringContainsString('X-VS:123456', $data);
    }

    public function testEurIbanReturnsSepaPayload(): void
    {
        $request = new PaymentRequest(
            amount: 100.0,
            currency: PaymentRequest::CURRENCY_EUR,
            message: 'Test',
            iban: 'CZ6508000000192000145399',
            bic: 'GIBACZPX',
            recipientName: 'Jan Novak',
        );

        $data = $request->getQrCodeData();

        $this->assertStringStartsWith('BCD', $data);
        $this->assertStringContainsString('EUR', $data);
        $this->assertStringContainsString('Jan Novak', $data);
    }
}
