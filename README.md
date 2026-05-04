# CzEurQrCode

thanks to [Endroid QR Code]( https://github.com/endroid/qr-code ) for QR code generation

thanks to [sunfoxcz/spayd-php](https://github.com/sunfoxcz/spayd-php) for SPAYD payment request generation

thanks to [rikudou/skqrpayment](https://github.com/RikudouSage/QrPaymentSK) for Slovak Pay by Square generation


## Popis

Balíček pro generování QR kódů s automatickou detekcí typu platby

Tento balíček vytváří QR kódy pro platby s inteligentní logikou:

- **Pro CZK platby** → Generuje **českou QR platbu** (SPAYD)
- **Pro EUR + `country: 'SK'`** → Generuje **slovenskou Pay by Square** platbu
- **Pro ostatní měny** → Generuje **SEPA platbu** (evropský standard)

## Funkčnost

Balíček rozpozná správný formát na základě parametrů (currency, country, IBAN/účet) a podle toho vybere odpovídající standard:

- **CZK + číslo účtu/kód banky** → Česká QR platba (SPAYD)
- **EUR + `country: 'SK'` + IBAN** → Slovenská QR platba (Pay by Square)
- **Ostatní (EUR/USD/... + IBAN)** → SEPA platba (evropský standard)
- Vrací hotový QR kód připravený k použití
- Možnost přizpůsobení vzhledu QR kódu (velikost, barvy, text)
- Podpora pro přidání loga do QR kódu

### Tabulka rozhodování

| currency | country | IBAN | účet+banka | Výsledek          |
|----------|---------|------|------------|-------------------|
| CZK      | –       | –    | ✓          | SPAYD (CZ)        |
| EUR      | `SK`    | ✓    | –          | Pay by Square (SK)|
| EUR/jiné | – / `null` | ✓ | –          | SEPA (EU)         |

## Použití

Ideální pro aplikace, které potřebují podporovat české QR platby, slovenské Pay by Square i mezinárodní SEPA převody v jednom řešení.
Balíček umožňuje snadné generování QR kódů pro různé platební standardy bez nutnosti psát vlastní logiku detekce.


## Instalace
```bash
composer require thecnology/czeurqrcode
```

> ℹ️ Pro slovenské Pay by Square platby je vyžadována binárka `xz` v systému (LZMA1 komprese).
> - Linux (Debian/Ubuntu): `apt install xz-utils`
> - macOS: `brew install xz`
> - Docker image v tomto repu už `xz-utils` obsahuje.

## Instalace skrz Docker

```bash
docker pull djvitto/czeurqrcode:latest
docker run -d -p 8080:80 --name czeurqrcode djvitto/czeurqrcode:latest
```

Image obsahuje HTTP endpoint, který přijímá URL parametry a vrací PNG s QR kódem.

### Docker URL příklady

#### CZ — Česká QR platba (SPAYD)
```
http://localhost:8080/?bankCode=2010&accountNumber=123456789&amount=100&currency=CZK&vs=123456789&message=Test&size=300&label=Děkujeme!
```

#### SK — Slovenská QR platba (Pay by Square)
Vyžaduje `country=SK` + IBAN. Bez `country=SK` by se vygenerovala SEPA.
```
http://localhost:8080/?amount=100&currency=EUR&country=SK&iban=SK6807200002891987426353&vs=123456&message=Platba&size=300&label=Ďakujeme!
```

#### EU — SEPA platba
```
http://localhost:8080/?amount=100&currency=EUR&iban=CZ6508000000192000145399&swift=GIBACZPX&recipientName=Jan%20Novak&message=Test&size=300&label=Děkujeme!
```

### Podporované URL parametry

| Parametr        | Povinný            | Popis                                                |
|-----------------|--------------------|------------------------------------------------------|
| `amount`        | ✓                  | Částka                                               |
| `currency`      | ✓                  | `CZK` / `EUR` / `USD`                                |
| `country`       | – (jen pro SK)     | `SK` vynutí Pay by Square, `CZ` ponechá SPAYD        |
| `bankCode`      | – (jen pro CZ)     | Kód banky (CZ)                                       |
| `accountNumber` | – (jen pro CZ)     | Číslo účtu (CZ)                                      |
| `iban`          | – (povinný pro SK/EU) | IBAN                                              |
| `swift`         | – (pro SEPA)       | BIC/SWIFT kód                                        |
| `recipientName` | – (pro SEPA)       | Jméno příjemce                                       |
| `vs`            | –                  | Variabilní symbol                                    |
| `message`       | –                  | Zpráva (max 35 znaků)                                |
| `size`          | –                  | Velikost QR (50–300 px)                              |
| `label`         | –                  | Popisek pod QR kódem                                 |


## Příklady použití

### CZ — Česká QR platba (SPAYD)

Pro tuzemské CZK platby použij číslo účtu + kód banky. Knihovna vygeneruje SPAYD.

```php
$qr = new QrCodeGenerator();
$request = new PaymentRequest(
    amount: 100.00,
    currency: PaymentRequest::CURRENCY_CZK,
    message: 'Nejaka zprava co uvidi vlastnik uctu pri prijeti platby',
    accountNumber: '100000',
    bankCode: '2010',
    variableSymbol: '123456789',
);
header('Content-Type: image/png');
echo $qr->getQrCode(qrData: $request->getQrCodeData(), qrLabel: 'Děkujeme za zaplacení!')->getString();
```

### SK — Slovenská QR platba (Pay by Square)

Pro slovenské banky použij EUR + IBAN + parametr `country: COUNTRY_SK`.
Bez `country: 'SK'` by se vygenerovala SEPA, kterou slovenské banky méně podporují.

```php
$qr = new QrCodeGenerator();
$request = new PaymentRequest(
    amount: 100.00,
    currency: PaymentRequest::CURRENCY_EUR,
    message: 'Platba za sluzby',
    variableSymbol: '123456789',
    iban: 'SK6807200002891987426353',
    country: PaymentRequest::COUNTRY_SK, // klíčové: vynutí formát Pay by Square
);
header('Content-Type: image/png');
echo $qr->getQrCode(qrData: $request->getQrCodeData(), qrLabel: 'Ďakujeme!')->getString();
```

### EU — SEPA platba

Pro mezinárodní EUR / cizoměnové platby v rámci SEPA. Bez `country` parametru
(nebo s `country: null`) se použije evropský standard.

```php
$qr = new QrCodeGenerator();
$request = new PaymentRequest(
    amount: 100.00,
    currency: PaymentRequest::CURRENCY_EUR,
    message: 'Nejaka zprava co uvidi vlastnik uctu pri prijeti platby',
    iban: 'CZ6500000000000000000000',
    bic: 'FIOBCZPPXXX',
    recipientName: 'Recipient name', // Pouze pro SEPA QR
);
header('Content-Type: image/png');
echo $qr->getQrCode(qrData: $request->getQrCodeData(), qrLabel: 'Děkujeme za zaplacení!')->getString();
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