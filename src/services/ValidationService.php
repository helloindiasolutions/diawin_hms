<?php
/**
 * Validation Service
 * Handles input validation
 */

declare(strict_types=1);

namespace App\Services;

use Security;

class ValidationService
{
    private array $errors = [];
    private array $data = [];

    /**
     * Validate data against rules
     */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        $this->data = $data;

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply single validation rule
     */
    private function applyRule(string $field, mixed $value, string $rule): void
    {
        $params = [];
        
        if (strpos($rule, ':') !== false) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        $method = 'validate' . ucfirst($rule);
        
        if (method_exists($this, $method)) {
            $this->$method($field, $value, $params);
        }
    }

    /**
     * Required validation
     */
    private function validateRequired(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }

    /**
     * Email validation
     */
    private function validateEmail(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'Please enter a valid email address');
        }
    }

    /**
     * Minimum length validation
     */
    private function validateMin(string $field, mixed $value, array $params): void
    {
        $min = (int) ($params[0] ?? 0);
        
        if (!empty($value) && strlen((string) $value) < $min) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must be at least $min characters");
        }
    }

    /**
     * Maximum length validation
     */
    private function validateMax(string $field, mixed $value, array $params): void
    {
        $max = (int) ($params[0] ?? 0);
        
        if (!empty($value) && strlen((string) $value) > $max) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must not exceed $max characters");
        }
    }

    /**
     * Numeric validation
     */
    private function validateNumeric(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a number');
        }
    }

    /**
     * Integer validation
     */
    private function validateInteger(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be an integer');
        }
    }

    /**
     * Alpha validation (letters only)
     */
    private function validateAlpha(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && !ctype_alpha($value)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must contain only letters');
        }
    }

    /**
     * Alphanumeric validation
     */
    private function validateAlphanumeric(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && !ctype_alnum($value)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must contain only letters and numbers');
        }
    }

    /**
     * URL validation
     */
    private function validateUrl(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'Please enter a valid URL');
        }
    }

    /**
     * Confirmed validation (field_confirmation must match)
     */
    private function validateConfirmed(string $field, mixed $value, array $params): void
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;
        
        if ($value !== $confirmValue) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' confirmation does not match');
        }
    }

    /**
     * In array validation
     */
    private function validateIn(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && !in_array($value, $params)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be one of: ' . implode(', ', $params));
        }
    }

    /**
     * Regex validation
     */
    private function validateRegex(string $field, mixed $value, array $params): void
    {
        $pattern = $params[0] ?? '';
        
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' format is invalid');
        }
    }

    /**
     * Date validation
     */
    private function validateDate(string $field, mixed $value, array $params): void
    {
        $format = $params[0] ?? 'Y-m-d';
        
        if (!empty($value)) {
            $date = \DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a valid date');
            }
        }
    }

    /**
     * No XSS validation
     */
    private function validateNoXss(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && Security::detectXss((string) $value)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' contains invalid characters');
        }
    }

    /**
     * No SQL injection validation
     */
    private function validateNoSql(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && Security::detectSqlInjection((string) $value)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' contains invalid characters');
        }
    }

    /**
     * Add error message
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error for each field
     */
    public function getFirstErrors(): array
    {
        $firstErrors = [];
        foreach ($this->errors as $field => $messages) {
            $firstErrors[$field] = $messages[0] ?? '';
        }
        return $firstErrors;
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }
}
