<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class LongitudeRule implements Rule
{
    /**
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $validator = Validator::make(
            ['latitude' => $value],
            [
                'latitude' => [
                    'numeric',
                    'min:-180',
                    'max:180',
                ],
            ]
        );

        return $validator->passes();
    }

    public function message(): string
    {
        return __('validation.longitude');
    }
}
