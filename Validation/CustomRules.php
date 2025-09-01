<?php

namespace App\Validation;

class CustomRules
{
    /**
     * Compara un string con un valor guardado en la sesión.
     *
     * @param string $str   El valor del campo del formulario (el captcha que ingresó el usuario).
     * @param string $field El parámetro de la regla (en nuestro caso, 'captcha_word').
     * @param array  $data  Todos los datos del formulario.
     *
     * @return bool Verdadero si los valores coinciden.
     */
    public function matches_session(string $str, string $field, array $data): bool
    {
        // Compara el valor del campo ($str) con el valor guardado en la sesión.
        // strtolower() se usa para que la comparación no distinga entre mayúsculas y minúsculas.
        return strtolower($str) === strtolower(session()->get($field));
    }
}