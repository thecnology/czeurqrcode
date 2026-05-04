<?php

declare(strict_types=1);

namespace thecnology\czeurqrcode;

use Rikudou\Iban\Iban\IBAN;
use rikudou\SkQrPayment\QrPayment;
use SepaQr\SepaQrData;
use Sunfox\Spayd\Model\CzechAccount;
use Sunfox\Spayd\Spayd;
use Sunfox\Spayd\Utilities\IbanUtilities;

class PaymentRequestFactory
{
    /**
     * Vytvoří instanci Spayd
     */
    public function createSpayd(): Spayd
    {
        return new Spayd();
    }

    /**
     * Vytvoří instanci SepaQrData
     */
    public function createSepaQrData(): SepaQrData
    {
        return new SepaQrData();
    }

    /**
     * Vytvoří instanci CzechAccount
     */
    public function createCzechAccount(string $account,string $bankCode): CzechAccount
    {
        return new CzechAccount("{$account}/{$bankCode}");
    }

    /**
     * Vypočítá IBAN z českého bankovního účtu
     */
    public function computeIban(CzechAccount $account): string
    {
        return IbanUtilities::computeIbanFromBankAccount($account);
    }

    /**
     * Vytvoří instanci QrPayment pro slovenskou Pay by Square platbu
     */
    public function createPayBySquare(string $iban): QrPayment
    {
        return new QrPayment(new IBAN($iban));
    }
}