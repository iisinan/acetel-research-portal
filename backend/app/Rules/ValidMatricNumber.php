<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidMatricNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valueStr = (string) $value;

        if (strlen($valueStr) < 6) {
            $fail('The matriculation number is too short.');
            return;
        }

        // Match format: One or more letters, exactly 2 digits for year, 1 digit for batch, then remaining digits.
        // e.g. ACE2110003 -> ACE (letters), 21 (year), 1 (batch), 0003 (rest)
        if (preg_match('/^[A-Za-z]+(\d{2})(\d)\d*$/', $valueStr, $matches)) {
            $batchStr = $matches[2];
            
            if (!in_array($batchStr, ['1', '2'])) {
                $fail('The matriculation number is incorrect. After the year (e.g., ACE21...), the next character must be 1 or 2 to signify the first or second batch.');
            }
        } else {
            $fail('The matriculation number format is invalid. It should start with letters, followed by a 2-digit year, a batch number (1 or 2), and then digits.');
        }
    }
}
