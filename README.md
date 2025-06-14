# CzEurQrCode 

## Popis

Balíček pro generování QR kódů s automatickou detekcí typu platby

Tento balíček vytváří QR kódy pro platby s inteligentní logikou:

- **Pro CZK platby** → Generuje **QR platbu** (český standard)
- **Pro ostatní měny** → Generuje **SEPA platbu** (evropský standard)

## Funkčnost

Balíček automaticky rozpozná měnu z obsahu platebních údajů a podle toho vybere odpovídající formát:

- Detekuje měnu z platebních informací
- Aplikuje správný standard (QR platba vs SEPA)
- Vrací hotový QR kód připravený k použití

## Použití

Ideální pro aplikace, které potřebují podporovat jak české QR platby, tak mezinárodní SEPA převody v jednom řešení.


## Instalace
```bash
composer require thecnology/czeurqrcode
```


## Příklad použití

```php
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



```
