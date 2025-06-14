# CzEurQrCode

thanks to [Endroid QR Code]( https://github.com/endroid/qr-code ) for QR code generation 

thanks to [sunfoxcz/spayd-php](https://github.com/sunfoxcz/spayd-php) for SPAYD payment request generation


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
- Možnost přizpůsobení vzhledu QR kódu (velikost, barvy, text)
- Podpora pro české QR platby (SPAYD) a mezinárodní SEPA platby
- Podpora pro přidání loga do QR kódu

## Použití

Ideální pro aplikace, které potřebují podporovat jak české QR platby, tak mezinárodní SEPA převody v jednom řešení.
Proto jsem si vytvořil tento balíček, který umožňuje snadné generování QR kódů pro různé platební standardy bez nutnosti psát vlastní logiku pro detekci měny a formátu platby.


## Instalace
```bash
composer require thecnology/czeurqrcode
```


## Příklad použití

```php
//pro generování QR kódu pro CZK platbu
$qr = new QrCodeGenerator();
$request=new PaymentRequest(
    amount: 100.00, // Amount
    currency: PaymentRequest::CURRENCY_CZK, // Currency
    message: 'Nejaka zprava co uvidi vlastnik uctu pri prijeti platby', // Message
    accountNumber: '100000', // Account number
    bankCode: '2010', // Bank code
    variableSymbol: '123456789', // Variable symbol
);
header('Content-Type: '.'image/png');
echo $qr->getQrCode(qrData: $request->getQrCodeData(),qrLabel: 'Děkujeme za zaplacení!')->getString();
```

```php
//pro generování QR kódu pro SEPA platbu
$qr = new QrCodeGenerator();
$request=new PaymentRequest(
    amount: 100.00, // Amount
    currency: PaymentRequest::CURRENCY_EUR, // Currency
    message: 'Nejaka zprava co uvidi vlastnik uctu pri prijeti platby', // Message
    iban: 'CZ650 0000000000000000000000', // IBAN
    bic: 'FIOBCZPPXXX', // BIC
    recipientName: 'Recipient name', // Recipient name - Only for SEPA QR

);
header('Content-Type: '.'image/png');
echo $qr->getQrCode(qrData: $request->getQrCodeData(),qrLabel: 'Děkujeme za zaplacení!')->getString();
```

```php
//moznost konfigurovat vzhled QR kódu - logo
$logo = new Logo(
    path: __DIR__.'/assets/bender.png',
    resizeToWidth: 50,
    punchoutBackground: true
);
//moznost konfigurovat vzhled QR kódu
$qr = new QrCodeGenerator(
    size: 300, // Size of the QR code in pixels
    margin: 10, // Margin around the QR code
    logo: $logo, // Logo to be added to the QR code
    errorCorrectionLevel: ErrorCorrectionLevel::Low,
    roundBlockSizeMode: RoundBlockSizeMode::Margin,
    foregroundColor: new Endroid\QrCode\Color\Color(0,0,0), // Foreground color in hex format
    backgroundColor: new Endroid\QrCode\Color\Color(255,255,244), // Background color in hex format
    labelColor: new Endroid\QrCode\Color\Color(100,0,0) // Color for the label text
);


```