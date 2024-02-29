<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Platform implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $value=='web' || $value=='mobile';
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute hanya diperbolehkan web atau mobile';
    }
}
