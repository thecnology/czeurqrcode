<?php

declare(strict_types=1);

namespace Tests\czeurqrcode;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use SepaQr\SepaQrData;
use Sunfox\Spayd\Model\CzechAccount;
use Sunfox\Spayd\Spayd;
use UQR\Factory\PaymentRequestFactory;
use UQR\PaymentRequest;

class PaymentRequestTest extends TestCase
{
    private PaymentRequestFactory|MockObject $factoryMock;

    protected function setUp(): void
    {
        $this->factoryMock = $this->createMock(PaymentRequestFactory::class);
    }

    /**
     * @test
     */
    public function testGetQrCodeDataReturnsSpaydForCzechAccountWithCZK(): void
    {
        // Arrange
        $expectedSpaydString = 'SPD*1.0*ACC:CZ1234567890*AM:100.0*CC:CZK*X-VS:123456';

        $spaydMock = $this->createMock(Spayd::class);
        $spaydMock->expects($this->exactly(4))
            ->method('add')
            ->withConsecutive(
                ['AM', '100.0'],
                ['CC', 'CZK'],
                ['X-VS', '123456'],
                ['ACC', 'CZ1234567890']
            );
        $spaydMock->expects($this->once())
            ->method('generate')
            ->willReturn($expectedSpaydString);

        $accountMock = $this->createMock(CzechAccount::class);

        $this->factoryMock->expects($this->once())
            ->method('createSpayd')
            ->willReturn($spaydMock);

        $this->factoryMock->expects($this->once())
            ->method('createCzechAccount')
            ->with('1234567890/0800')
            ->willReturn($accountMock);

        $this->factoryMock->expects($this->once())
            ->method('computeIban')
            ->with($accountMock)
            ->willReturn('CZ1234567890');

        $paymentRequest = new PaymentRequest(
            100.0,
            PaymentRequest::CURRENCY_CZK,
            '',
            '1234567890',
            '0800',
            '123456',
            null,
            null,
            null,
            $this->factoryMock
        );

        // Act
        $result = $paymentRequest->getQrCodeData();

        // Assert
        $this->assertEquals($expectedSpaydString, $result);
    }

    /**
     * @test
     */
    public function testGetQrCodeDataReturnsSepaForIbanAccount(): void
    {
        // Arrange
        $expectedSepaString = 'BCD001...';

        $sepaMock = $this->getMockBuilder(SepaQrData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sepaMock->expects($this->once())->method('setName')->with('Jan Novák')->willReturnSelf();
        $sepaMock->expects($this->once())->method('setIban')->with('CZ1234567890')->willReturnSelf();
        $sepaMock->expects($this->once())->method('setBic')->with('GIBACZPX')->willReturnSelf();
        $sepaMock->expects($this->once())->method('setCurrency')->with('EUR')->willReturnSelf();
        $sepaMock->expects($this->once())->method('setRemittanceText')->with('')->willReturnSelf();
        $sepaMock->expects($this->once())->method('setAmount')->with(100.0)->willReturnSelf();
        $sepaMock->expects($this->once())->method('__toString')->willReturn($expectedSepaString);

        $this->factoryMock->expects($this->once())
            ->method('createSepaQrData')
            ->willReturn($sepaMock);

        $paymentRequest = new PaymentRequest(
            100.0,
            PaymentRequest::CURRENCY_EUR,
            '',
            null,
            null,
            null,
            'CZ1234567890',
            'GIBACZPX',
            'Jan Novák',
            $this->factoryMock
        );

        // Act
        $result = $paymentRequest->getQrCodeData();

        // Assert
        $this->assertEquals($expectedSepaString, $result);
    }

    // Další testy...
}