<?php
class GetValidator {
    private $errors = [];
    private $data = [];

    public function __construct($getData = null) {
        $this->data = $getData ?? $_GET;
    }

    // Validace povinného parametru
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
            $this->errors[$field][] = $message ?? "Pole {$field} je povinné";
        }
        return $this;
    }

    // Validace emailu
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?? "Pole {$field} musí být platný email";
        }
        return $this;
    }

    // Validace čísla
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = $message ?? "Pole {$field} musí být číslo";
        }
        return $this;
    }

    // Validace celého čísla
    public function integer($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->errors[$field][] = $message ?? "Pole {$field} musí být celé číslo";
        }
        return $this;
    }

    // Validace minimální délky
    public function minLength($field, $min, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field][] = $message ?? "Pole {$field} musí mít alespoň {$min} znaků";
        }
        return $this;
    }

    // Validace maximální délky
    public function maxLength($field, $max, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field][] = $message ?? "Pole {$field} může mít maximálně {$max} znaků";
        }
        return $this;
    }

    // Validace rozsahu hodnot
    public function between($field, $min, $max, $message = null) {
        if (isset($this->data[$field])) {
            $value = (float)$this->data[$field];
            if ($value < $min || $value > $max) {
                $this->errors[$field][] = $message ?? "Pole {$field} musí být mezi {$min} a {$max}";
            }
        }
        return $this;
    }

    // Validace regulárním výrazem
    public function regex($field, $pattern, $message = null) {
        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field][] = $message ?? "Pole {$field} má neplatný formát";
        }
        return $this;
    }

    // Validace povolených hodnot
    public function in($field, $allowedValues, $message = null) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $allowedValues)) {
            $this->errors[$field][] = $message ?? "Pole {$field} má neplatnou hodnotu";
        }
        return $this;
    }

    // Kontrola, zda validace prošla
    public function isValid() {
        return empty($this->errors);
    }

    // Získání chyb
    public function getErrors() {
        return $this->errors;
    }

    // Získání validovaných dat
    public function getData($field = null) {
        if ($field) {
            return $this->data[$field] ?? null;
        }
        return $this->data;
    }

    // Sanitizace dat
    public function sanitize($field) {
        if (isset($this->data[$field])) {
            $this->data[$field] = htmlspecialchars(trim($this->data[$field]), ENT_QUOTES, 'UTF-8');
        }
        return $this;
    }
}
