<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidABN implements ValidationRule
{

    /**
     * Determine if the ABN format is valid.
     *
     * The 11 digit ABN is structured as a 9 digit identifier with two leading check digits.
     * The leading check digits are derived using a modulus 89 (remainder after dividing by 89) calculation.
     * reference: https://abr.business.gov.au/Help/AbnFormat
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $abn = trim($value);

	if (! ctype_digit($abn) || strlen($abn) !== 11) {
            $fail($this->message());
            return;
        }

        $nums = array_map('intval', str_split($abn, 1));
        $nums[0] -= 1;

        $weights = [10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19];

        $sum = 0;
        foreach ($nums as $pos => $num) {
            $sum += $num * $weights[$pos];
        }

        if ($sum % 89 !== 0) {
            $fail($this->message());
            return;
        }
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return __('validation.abn');
    }
}
