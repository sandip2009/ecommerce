<?php

namespace App\Core;

class Validator
{
    private array $errors = [];

    public function required(
        string $field,
        mixed $value
    ): self {
        if (
            $value === null ||
            $value === '' ||
            (
                is_string($value) &&
                trim($value) === ''
            )
        ) {
            $this->errors[$field][] =
                'The field is required.';
        }

        return $this;
    }

    public function string(
        string $field,
        mixed $value
    ): self {
        if (
            $value !== null &&
            !is_string($value)
        ) {
            $this->errors[$field][] =
                'The field must be a string.';
        }

        return $this;
    }

    public function maxLength(
        string $field,
        mixed $value,
        int $max
    ): self {
        if (
            is_string($value) &&
            mb_strlen($value) > $max
        ) {
            $this->errors[$field][] =
                "The field may not exceed {$max} characters.";
        }

        return $this;
    }

    public function numeric(
        string $field,
        mixed $value
    ): self {
        if (
            $value !== null &&
            !is_numeric($value)
        ) {
            $this->errors[$field][] =
                'The field must be numeric.';
        }

        return $this;
    }

    public function integer(
        string $field,
        mixed $value
    ): self {
        if (
            $value !== null &&
            filter_var(
                $value,
                FILTER_VALIDATE_INT
            ) === false
        ) {
            $this->errors[$field][] =
                'The field must be an integer.';
        }

        return $this;
    }

    public function min(
        string $field,
        mixed $value,
        float $min
    ): self {
        if (
            is_numeric($value) &&
            (float) $value < $min
        ) {
            $this->errors[$field][] =
                "The minimum value is {$min}.";
        }

        return $this;
    }

    public function boolean(string $field, mixed $value): self {
        if (
            $value !== null &&
            !in_array(
                $value,
                [0, 1, true, false, '0', '1'],
                true
            )
        ) {
            $this->errors[$field][] =
                'The field must be boolean.';
        }

        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}