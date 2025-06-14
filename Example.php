<?php

declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/QR/QrCodeGenerator.php';
include __DIR__ . '/QR/PaymentRequest.php';
include __DIR__ . '/QR/PaymentRequestFactory.php';


use thecnology\czeurqrcode\PaymentRequestFactory;
use thecnology\czeurqrcode\QrCodeGenerator;
use thecnology\czeurqrcode\PaymentRequest;

$qr = new QrCodeGenerator();
$request=new PaymentRequest(
    amount: 100.00, // Amount
    currency: PaymentRequest::CURRENCY_CZK, // Currency
    message: 'Nejaka zprava co uvidi vlastnik uctu pri prijeti platby', // Message
    accountNumber: '100000', // Account number
    bankCode: '2010', // Bank code
    variableSymbol: '123456789', // Variable symbol
    iban: 'CZ74 2010 0000 0022 0083 3794', // IBAN
    bic: 'FIOBCZPPXXX', // BIC
    recipientName: 'Name Of Person Who Pay', // Recipient name - Only for SEPA QR
    factory: new PaymentRequestFactory() // Factory for creating payment requests
);
header('Content-Type: '.'image/png');
echo $qr->getQrCode(qrData: $request->getQrCodeData(),qrLabel: 'Děkujeme za zaplacení!')->getString();





