<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class LatitudeRule implements Rule
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
                    'min:-90',
                    'max:90',
                ],
            ]
        );

        return $validator->passes();
    }

    public function message(): string
    {
        return __('validation.latitude');
    }
}
