<?php

namespace App\Core;

class Validator
{
    /**
     * Validate data against a set of rules.
     *
     * Supported rules: required, max:{n}, email, in:{val1},{val2},..., integer
     *
     * @param array $data  Input data (associative array)
     * @param array $rules Rules keyed by field name, pipe-separated (e.g. ['title' => 'required|max:255'])
     * @return array       Errors keyed by field name, each an array of messages. Empty if valid.
     */
    public function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);

            foreach ($fieldRules as $rule) {
                $error = $this->applyRule($field, $data[$field] ?? null, $rule, $data);

                if ($error !== null) {
                    $errors[$field][] = $error;
                }
            }
        }

        return $errors;
    }

    private function applyRule(string $field, mixed $value, string $rule, array $data): ?string
    {
        if (str_starts_with($rule, 'max:')) {
            $max = (int) substr($rule, 4);
            if ($value !== null && is_string($value) && mb_strlen($value) > $max) {
                return "The {$field} must not exceed {$max} characters.";
            }
            return null;
        }

        if (str_starts_with($rule, 'in:')) {
            $allowed = explode(',', substr($rule, 3));
            if ($value !== null && !in_array($value, $allowed, true)) {
                $list = implode(', ', $allowed);
                return "The {$field} must be one of: {$list}.";
            }
            return null;
        }

        return match ($rule) {
            'required' => $this->ruleRequired($field, $value),
            'email'    => $this->ruleEmail($field, $value),
            'integer'  => $this->ruleInteger($field, $value),
            default    => null,
        };
    }

    private function ruleRequired(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return "The {$field} field is required.";
        }
        return null;
    }

    private function ruleEmail(string $field, mixed $value): ?string
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "The {$field} must be a valid email address.";
        }
        return null;
    }

    private function ruleInteger(string $field, mixed $value): ?string
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            return "The {$field} must be an integer.";
        }
        return null;
    }
}
