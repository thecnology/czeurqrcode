<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

include "GetValidator.php";
include "BankPaymentValidator.php";





$validator = new BankPaymentValidator();

$validator
    // Kód banky - volitelný (povinný jen pro CZK / SPAYD; SK Pay by Square jej nepotřebuje)
    ->czechBankCode('bankCode')

    // Částka - povinná
    ->required('amount', 'Částka je povinná')
    ->amount('amount', 1, 1000000, 'Částka musí být mezi 1 a 1 000 000')

    // Měna - povinná
    ->required('currency', 'Měna je povinná')
    ->currency('currency', ['CZK', 'EUR', 'USD'])

    // Země - volitelná, vynutí formát QR (SK = Pay by Square, CZ = SPAYD)
    ->country('country', ['CZ', 'SK'])

    // Variabilní symbol - volitelný
    ->variableSymbol('vs')
    ->iban('iban')
    ->swift('swift')
    ->recipient('swift')

    // Zpráva pro příjemce - volitelná
    ->paymentMessage('message', 35, 'Zpráva může mít max. 35 znaků pro bankovní převod')
    ->sanitize('message')



    // Velikost - volitelná (pro QR kód)
    ->size('size', 50, 300)
    ->label('label', 'Děkujeme za platbu!')

    // Číslo účtu - volitelné (povinné jen pro CZK SPAYD)
    ->czechAccountNumber('accountNumber');

// Kontrola výsledku
if ($validator->isValid()) {
    // Získání validovaných dat
    $paymentData = [
        'bankCode' => $validator->getData('bankCode'),
        'bankName' => $validator->getBankName($validator->getData('bankCode')),
        'amount' => (float)$validator->getData('amount'),
        'currency' => $validator->getData('currency'),
        'country' => $validator->getData('country'),
        'variableSymbol' => $validator->getData('vs'),
        'message' => $validator->getData('message'),
        'size' => (int)$validator->getData('size'),
        'accountNumber' => $validator->getData('accountNumber'),
        'iban' => $validator->getData('iban'),
        'bic' => $validator->getData('bic') ?: $validator->getData('swift'),
        'recipientName' => $validator->getData('recipientName') ?: $validator->getData('recipient'),
        'label' => $validator->getData('label') ?: 'Děkujeme za platbu!'

    ];

    $request = new thecnology\czeurqrcode\PaymentRequest(
        amount: $paymentData['amount'],
        currency: $paymentData['currency'],
        message: $paymentData['message'] ?? '',
        accountNumber: $paymentData['accountNumber'] ?? null,
        bankCode: $paymentData['bankCode'] ?? null,
        variableSymbol: $paymentData['variableSymbol'] ?? null,
        iban: $paymentData['iban'] ?? null,
        bic: $paymentData['bic'] ?? null,
        recipientName: $paymentData['recipientName'] ?? null,
        country: $paymentData['country'] ?? null,
    );


    $qr = new thecnology\czeurqrcode\QrCodeGenerator();
    $imgData = $qr->getQrCode(qrData: $request->getQrCodeData(), qrLabel: $paymentData['label'])->getString();
    header('Content-Type: '.'image/png');
    echo $imgData;

} else {
    echo "❌ Chyby validace:\n";
    foreach ($validator->getErrors() as $field => $errors) {
        echo "• {$field}: " . implode(', ', $errors) . "\n";
    }

    // Pro AJAX odpověď
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'errors' => $validator->getErrors()
    ]);
}