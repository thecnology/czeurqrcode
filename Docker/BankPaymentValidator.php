<?php
class BankPaymentValidator extends GetValidator {

    // Validace českého kódu banky
    public function czechBankCode($field, $message = null) {
        if (isset($this->data[$field])) {
            $bankCode = $this->data[$field];

            // Musí být 4 číslice
            if (!preg_match('/^\d{4}$/', $bankCode)) {
                $this->errors[$field][] = $message ?? "Kód banky musí být 4 číslice";
                return $this;
            }

            // Seznam platných kódů českých bank
            $validBankCodes = [
                '0100', '0300', '0600', '0710', '0800', '2010', '2020', '2030',
                '2060', '2070', '2100', '2200', '2220', '2240', '2250', '2260',
                '2275', '2600', '2700', '3030', '3050', '3060', '4000', '4300',
                '5500', '5800', '6000', '6100', '6200', '6210', '6300', '6700',
                '6800', '7910', '7940', '7950', '7960', '7970', '7980', '7990',
                '8030', '8040', '8060', '8090', '8150', '8190', '8200', '8215',
                '8220', '8230', '8240', '8250', '8260', '8265', '8270', '8280',
                '8290', '8291', '8292', '8293', '8294'
            ];

            if (!in_array($bankCode, $validBankCodes)) {
                $this->errors[$field][] = $message ?? "Neplatný kód banky: {$bankCode}";
            }
        }
        return $this;
    }

    // Validace částky
    public function amount($field, $min = 0.01, $max = 999999999.99, $message = null) {
        if (isset($this->data[$field])) {
            $amount = $this->data[$field];

            // Kontrola, zda je to číslo
            if (!is_numeric($amount)) {
                $this->errors[$field][] = $message ?? "Částka musí být číslo";
                return $this;
            }

            $amount = (float)$amount;

            // Kontrola rozsahu
            if ($amount < $min || $amount > $max) {
                $this->errors[$field][] = $message ?? "Částka musí být mezi {$min} a {$max} Kč";
            }

            // Kontrola maximálně 2 desetinná místa
            if (round($amount, 2) != $amount) {
                $this->errors[$field][] = $message ?? "Částka může mít maximálně 2 desetinná místa";
            }
        }
        return $this;
    }

    // Validace měny
    public function currency($field, $allowedCurrencies = ['CZK', 'EUR', 'USD'], $message = null) {
        if (isset($this->data[$field])) {
            $currency = strtoupper($this->data[$field]);
            if (!in_array($currency, $allowedCurrencies)) {
                $this->errors[$field][] = $message ?? "Neplatná měna. Povolené: " . implode(', ', $allowedCurrencies);
            }
        }
        return $this;
    }

    // Validace variabilního symbolu
    public function variableSymbol($field, $message = null) {
        if (isset($this->data[$field])) {
            $vs = $this->data[$field];

            // Může být prázdný nebo číslo do 10 číslic
            if (!empty($vs) && (!preg_match('/^\d{1,10}$/', $vs))) {
                $this->errors[$field][] = $message ?? "Variabilní symbol musí být číslo s maximálně 10 číslicemi";
            }
        }
        return $this;
    }

    // Validace čísla účtu
    public function czechAccountNumber($field, $message = null) {
        if (isset($this->data[$field])) {
            $accountNumber = $this->data[$field];

            // Formát: [prefix-]number nebo jen number
            if (!preg_match('/^(\d{1,6}-)?(\d{2,10})$/', $accountNumber, $matches)) {
                $this->errors[$field][] = $message ?? "Neplatný formát čísla účtu";
                return $this;
            }

            // Validace kontrolního součtu podle české normy
            $prefix = isset($matches[1]) ? rtrim($matches[1], '-') : '';
            $number = $matches[2];

            // Kontrola prefix (pokud existuje)
            if (!empty($prefix)) {
                $prefixWeights = [10, 5, 8, 4, 2, 1];
                $prefixSum = 0;
                $prefixPadded = str_pad($prefix, 6, '0', STR_PAD_LEFT);

                for ($i = 0; $i < 6; $i++) {
                    $prefixSum += (int)$prefixPadded[$i] * $prefixWeights[$i];
                }

                if ($prefixSum % 11 !== 0) {
                    $this->errors[$field][] = $message ?? "Neplatný prefix čísla účtu";
                    return $this;
                }
            }

            // Kontrola hlavního čísla
            $numberWeights = [6, 3, 7, 9, 10, 5, 8, 4, 2, 1];
            $numberSum = 0;
            $numberPadded = str_pad($number, 10, '0', STR_PAD_LEFT);

            for ($i = 0; $i < 10; $i++) {
                $numberSum += (int)$numberPadded[$i] * $numberWeights[$i];
            }

            if ($numberSum % 11 !== 0) {
                $this->errors[$field][] = $message ?? "Neplatné číslo účtu";
            }
        }
        return $this;
    }

    // Validace zprávy pro příjemce
    public function paymentMessage($field, $maxLength = 140, $message = null) {
        if (isset($this->data[$field])) {
            $msg = $this->data[$field];

            // Kontrola délky
            if (strlen($msg) > $maxLength) {
                $this->errors[$field][] = $message ?? "Zpráva může mít maximálně {$maxLength} znaků";
            }

            // Kontrola povolených znaků (základní ASCII bez speciálních znaků)
            if (!preg_match('/^[a-zA-Z0-9\s\.\,\-\_\(\)\[\]]*$/', $msg)) {
                $this->errors[$field][] = $message ?? "Zpráva obsahuje nepovolené znaky";
            }
        }
        return $this;
    }

    // Validace velikosti (pro QR kód nebo podobně)
    public function size($field, $min = 50, $max = 500, $message = null) {
        if (isset($this->data[$field])) {
            $size = (int)$this->data[$field];

            if ($size < $min || $size > $max) {
                $this->errors[$field][] = $message ?? "Velikost musí být mezi {$min} a {$max}";
            }
        }
        return $this;
    }

    public function iban($field)
    {
        if (isset($this->data[$field])) {
            $iban = $this->data[$field];

            // Kontrola formátu IBAN
            if (!preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{4,30}$/', $iban)) {
                $this->errors[$field][] = "IBAN má neplatný formát";
                return $this;
            }

            // Kontrola délky IBAN
            if (strlen($iban) < 15 || strlen($iban) > 34) {
                $this->errors[$field][] = "IBAN musí mít mezi 15 a 34 znaky";
            }
        }
        return $this;
    }

    public function recipient($field)
    {
        if (isset($this->data[$field])) {
            $recipient = $this->data[$field];

            // Kontrola délky jména příjemce
            if (strlen($recipient) < 1 || strlen($recipient) > 140) {
                $this->errors[$field][] = "Jméno příjemce musí mít mezi 1 a 140 znaky";
            }

            // Kontrola povolených znaků (základní ASCII bez speciálních znaků)
            if (!preg_match('/^[a-zA-Z0-9\s\.\,\-\_\(\)\[\]]*$/', $recipient)) {
                $this->errors[$field][] = "Jméno příjemce obsahuje nepovolené znaky";
            }
        }
        return $this;
    }

    public function swift($field)
    {
        if (isset($this->data[$field])) {
            $swift = $this->data[$field];

            // Kontrola formátu SWIFT/BIC kódu
            if (!preg_match('/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/', $swift)) {
                $this->errors[$field][] = "SWIFT/BIC má neplatný formát";
            }
        }
        return $this;
    }

    public function label($field, $default = '', $message = null)
    {
        if (isset($this->data[$field])) {
            $label = $this->data[$field];

            // Kontrola délky štítku
            if (strlen($label) > 50) {
                $this->errors[$field][] = $message ?? "Štítek může mít maximálně 50 znaků";
            }
        }
        return $this;
    }
      // Získání názvu banky podle kódu
    public function getBankName($bankCode) {
        $banks = [
            '0100' => 'Komerční banka',
            '0300' => 'Československá obchodní banka',
            '0600' => 'MONETA Money Bank',
            '0710' => 'Česká národní banka',
            '0800' => 'Česká spořitelna',
            '2010' => 'Fio banka',
            '2020' => 'Bank of Tokyo-Mitsubishi UFJ (Holland)',
            '2060' => 'Citfin',
            '2070' => 'Moravský Peněžní Ústav',
            '2100' => 'Hypoteční banka',
            '2200' => 'Peněžní dům',
            '2220' => 'Artesa',
            '2240' => 'Poštová banka',
            '2250' => 'Banka Creditas',
            '2260' => 'NEY spořitelní družstvo',
            '2600' => 'Citibank Europe plc',
            '2700' => 'UniCredit Bank Czech Republic and Slovakia',
            '3030' => 'Air Bank',
            '3050' => 'BNP Paribas Personal Finance SA',
            '3060' => 'PKO BP S.A., Czech Branch',
            '5500' => 'Raiffeisenbank',
            '5800' => 'J&T BANKA',
            '6000' => 'PPF banka',
            '6100' => 'Equa bank',
            '6200' => 'COMMERZBANK Aktiengesellschaft',
            '6300' => 'BNP Paribas S.A.',
            '6700' => 'Všeobecná úverová banka',
            '6800' => 'Sberbank CZ',
            '7910' => 'Deutsche Bank Aktiengesellschaft Filiale Prag',
            '7940' => 'Waldviertler Sparkasse Bank AG',
            '7950' => 'Raiffeisen stavební spořitelna',
            '7960' => 'Českomoravská stavební spořitelna',
            '7970' => 'Wüstenrot - stavební spořitelna',
            '7980' => 'Wüstenrot hypoteční banka',
            '7990' => 'Modrá pyramida stavební spořitelna',
            '8030' => 'Volksbank Raiffeisenbank Nordoberpfalz eG pobočka Cheb',
            '8040' => 'Oberbank AG pobočka Česká republika',
            '8090' => 'Česká exportní banka',
            '8150' => 'HSBC Bank plc - pobočka Praha',
            '8190' => 'Sparkasse Oberlausitz-Niederschlesien',
            '8200' => 'PRIVAT BANK der Raiffeisenlandesbank Oberösterreich Aktiengesellschaft',
            '8220' => 'Payment Execution s.r.o.',
            '8230' => 'EURAM Bank GmbH.',
            '8240' => 'Raiffeisenbank im Stiftland eG pobočka Cheb',
            '8250' => 'Bank Gutmann Aktiengesellschaft',
            '8260' => 'SberBank Europe AG',
            '8265' => 'ATLANTIK finanční služby, a.s.',
            '8270' => 'Fairplay Capital s.r.o.',
            '8280' => 'B2B Pay s.r.o.',
            '8290' => 'Zuno Bank AG, organizační složka'
        ];

        return $banks[$bankCode] ?? 'Neznámá banka';
    }
}
