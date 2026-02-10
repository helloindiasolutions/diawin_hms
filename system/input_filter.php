<?php
/**
 * Input Filter System
 * Advanced input filtering and sanitization
 */

declare(strict_types=1);

class InputFilter
{
    /**
     * Filter and sanitize input data
     */
    public static function filter(array $data, array $rules): array
    {
        $filtered = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            if ($value === null) {
                continue;
            }
            
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            
            foreach ($ruleList as $rule) {
                $value = self::applyFilter($value, $rule);
            }
            
            $filtered[$field] = $value;
        }
        
        return $filtered;
    }

    /**
     * Apply single filter
     */
    private static function applyFilter(mixed $value, string $rule): mixed
    {
        $params = [];
        
        if (strpos($rule, ':') !== false) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        return match ($rule) {
            'trim' => is_string($value) ? trim($value) : $value,
            'lowercase' => is_string($value) ? strtolower($value) : $value,
            'uppercase' => is_string($value) ? strtoupper($value) : $value,
            'ucfirst' => is_string($value) ? ucfirst(strtolower($value)) : $value,
            'ucwords' => is_string($value) ? ucwords(strtolower($value)) : $value,
            'strip_tags' => is_string($value) ? strip_tags($value, $params[0] ?? '') : $value,
            'escape' => Security::escape($value),
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'email' => filter_var($value, FILTER_SANITIZE_EMAIL),
            'url' => filter_var($value, FILTER_SANITIZE_URL),
            'alpha' => preg_replace('/[^a-zA-Z]/', '', (string) $value),
            'alphanumeric' => preg_replace('/[^a-zA-Z0-9]/', '', (string) $value),
            'slug' => self::toSlug((string) $value),
            'numeric' => preg_replace('/[^0-9]/', '', (string) $value),
            'phone' => preg_replace('/[^0-9+\-\s()]/', '', (string) $value),
            'max' => self::truncate($value, (int) ($params[0] ?? 255)),
            'default' => $value ?? ($params[0] ?? null),
            'null_empty' => $value === '' ? null : $value,
            'array' => is_array($value) ? $value : [$value],
            'json' => self::parseJson($value),
            'date' => self::parseDate($value, $params[0] ?? 'Y-m-d'),
            'sanitize' => self::sanitize((string) $value),
            default => $value
        };
    }

    /**
     * Convert to slug
     */
    private static function toSlug(string $value): string
    {
        $value = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $value);
        $value = preg_replace('/[\s-]+/', '-', $value);
        $value = trim($value, '-');
        return strtolower($value);
    }

    /**
     * Truncate string
     */
    private static function truncate(mixed $value, int $length): mixed
    {
        if (!is_string($value)) {
            return $value;
        }
        
        return mb_strlen($value) > $length ? mb_substr($value, 0, $length) : $value;
    }

    /**
     * Parse JSON
     */
    private static function parseJson(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }
        
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    /**
     * Parse date
     */
    private static function parseDate(mixed $value, string $format): ?string
    {
        if (!is_string($value) || empty($value)) {
            return null;
        }
        
        $date = \DateTime::createFromFormat($format, $value);
        if (!$date) {
            $date = new \DateTime($value);
        }
        
        return $date ? $date->format($format) : null;
    }

    /**
     * General sanitization
     */
    private static function sanitize(string $value): string
    {
        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        
        // Remove invisible characters
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        
        // Normalize line endings
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        
        return trim($value);
    }

    /**
     * Get filtered input from request
     */
    public static function get(string $key, array $filters = [], mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        
        if ($value === null || empty($filters)) {
            return $value;
        }
        
        $filtered = self::filter([$key => $value], [$key => $filters]);
        return $filtered[$key] ?? $default;
    }

    /**
     * Get all filtered input
     */
    public static function all(array $rules): array
    {
        $input = array_merge($_GET, $_POST);
        return self::filter($input, $rules);
    }

    /**
     * Get JSON input filtered
     */
    public static function json(array $rules): array
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        return self::filter($input, $rules);
    }
}
