<?php

declare(strict_types=1);

namespace thecnology\czeurqrcode;

use DateTime;
use SepaQr\SepaQrData;
use Sunfox\Spayd\Model\CzechAccount;
use Sunfox\Spayd\Spayd;
use thecnology\czeurqrcode\PaymentRequestFactory;

class PaymentRequest
{
    public const CURRENCY_CZK = 'CZK';
    public const CURRENCY_EUR = 'EUR';

    private PaymentRequestFactory $factory;

    public function __construct(
        private float $amount,
        private string $currency = self::CURRENCY_CZK,
        private string $message = '',
        private ?string $accountNumber = null,
        private ?string $bankCode = null,
        private ?string $variableSymbol = null,
        private ?string $iban = null,
        private ?string $bic = null,
        private ?string $recipientName = null,
        ?PaymentRequestFactory $factory = null
    ) {
        $this->factory = $factory ?? new PaymentRequestFactory();
    }

    public function getQrCodeData(): string
    {
        if ($this->iban === null && ($this->accountNumber === null || $this->bankCode === null)) {
            throw new \InvalidArgumentException('Either IBAN or account number must be provided.');
        }
        if ($this->accountNumber !== null && $this->bankCode !== null && $this->currency === self::CURRENCY_CZK) {
            return $this->getSpaydString();
        }
        return $this->getSepaString();
    }

    private function getSpaydString(): string
    {
        $spayd = $this->factory->createSpayd();
        $spayd->add('AM', (string)$this->amount);
        $spayd->add('CC', $this->currency);
        $spayd->add('X-VS', $this->variableSymbol);

        $account = $this->factory->createCzechAccount($this->accountNumber,$this->bankCode);
        $spayd->add('ACC', $this->factory->computeIban($account));

        if ($this->message !== '') {
            $spayd->add('MSG', $this->formatMessage());
        }

        return $spayd->generate();
    }

    private function getSepaString(): string
    {
        $sepa = $this->factory->createSepaQrData();
        $sepa->setName($this->recipientName)
            ->setIban($this->iban)
            ->setBic($this->bic)
            ->setCurrency($this->currency)
            ->setInformation($this->formatMessage())
            ->setAmount($this->amount); // The amount in Euro

        return $sepa->__toString();
    }

    private function formatMessage(): string
    {
        if ($this->message === '') {
            return '';
        }
        $message = strtoupper($this->message);
        if (strlen($message) > 140) {
            $message = substr($message, 0, 140);
        }
        return $message;
    }
}