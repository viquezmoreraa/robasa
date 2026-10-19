<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * Acepta solo correos de proveedores conocidos; rechaza dominios inventados o desechables.
 */
class CorreoProveedorPermitido implements ValidationRule
{
    public const DOMINIOS = [
        'gmail.com',
        'hotmail.com', 'hotmail.es',
        'outlook.com', 'outlook.es',
        'live.com', 'msn.com',
        'yahoo.com', 'yahoo.es',
        'icloud.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $dominio = Str::lower(Str::afterLast($value, '@'));

        if (! in_array($dominio, self::DOMINIOS, true)) {
            $fail('Use un correo de Gmail, Hotmail, Outlook, Yahoo o iCloud.');
        }
    }
}
