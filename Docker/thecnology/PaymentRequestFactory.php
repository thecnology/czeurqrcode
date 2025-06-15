<?php

declare(strict_types=1);

namespace thecnology\czeurqrcode;

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
}