<?php

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // Wajib diisi
    public function required(string $field, string $label): self
    {
        $value = trim($this->data[$field] ?? '');
        if ($value === '') {
            $this->errors[$field] = "$label wajib diisi.";
        }
        return $this;
    }

    // Minimal karakter
    public function minLength(string $field, int $min, string $label): self
    {
        $value = $this->data[$field] ?? '';
        if (mb_strlen($value) < $min) {
            $this->errors[$field] = "$label minimal $min karakter.";
        }
        return $this;
    }

    // Maksimal karakter
    public function maxLength(string $field, int $max, string $label): self
    {
        $value = $this->data[$field] ?? '';
        if (mb_strlen($value) > $max) {
            $this->errors[$field] = "$label maksimal $max karakter.";
        }
        return $this;
    }

    // Validasi format email
    public function email(string $field, string $label): self
    {
        $value = $this->data[$field] ?? '';
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$label harus format email yang valid.";
        }
        return $this;
    }

    // Validasi regex (pola tertentu)
    public function pattern(string $field, string $pattern, string $message): self
    {
        $value = $this->data[$field] ?? '';
        if (!preg_match($pattern, $value)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    // Validasi angka dalam rentang tertentu
    public function numericBetween(string $field, float $min, float $max, string $label): self
    {
        $value = $this->data[$field] ?? '';
        if (!is_numeric($value) || $value < $min || $value > $max) {
            $this->errors[$field] = "$label harus antara $min dan $max.";
        }
        return $this;
    }

    // Cek apakah semua validasi lolos
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    // Ambil semua error
    public function getErrors(): array
    {
        return $this->errors;
    }

    // Ambil error satu field
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }
}
