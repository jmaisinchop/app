<?php
// app/Helpers/captcha_helper.php

use Gregwar\Captcha\CaptchaBuilder;

if (!function_exists('create_custom_captcha')) {
    /**
     * Crea un CAPTCHA personalizado y reutilizable con Gregwar's Captcha.
     *
     * @return string|null La imagen del CAPTCHA como datos en línea (inline data URI).
     */
    function create_custom_captcha(): ?string
    {
        try {
            // Crea una instancia del constructor de CAPTCHA
            $builder = new CaptchaBuilder;

            // --- INICIO DE LA PERSONALIZACIÓN VISUAL ---
            
            // 1. Establece el tamaño de la imagen
            $builder->build(160, 45);

            // 2. Define los colores para un mejor contraste
            $builder->setBackgroundColor(245, 245, 245); // Un fondo gris muy claro
            $builder->setTextColor(0, 70, 140);       // Texto azul oscuro

            // 3. Aplica la distorsión para dificultar la lectura a los bots
            $builder->setDistortion(true);
            
            // 4. Intenta suavizar la imagen final para mejorar la legibilidad
            $builder->setInterpolation(true);
            
            // --- FIN DE LA PERSONALIZACIÓN VISUAL ---

            // Guarda la frase correcta en la sesión para la validación
            session()->set('captcha_phrase', $builder->getPhrase());

            // Devuelve la imagen lista para ser insertada en una etiqueta <img>
            return $builder->inline();

        } catch (Exception $e) {
            // En caso de error, registra el problema y no muestra nada
            log_message('error', 'Error al crear el CAPTCHA: ' . $e->getMessage());
            return null;
        }
    }
}