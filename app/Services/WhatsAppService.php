<?php

namespace App\Services;

class WhatsAppService
{
    public static function phone(): string
    {
        return config('services.whatsapp.phone', '591XXXXXXXXX');
    }

    public static function message(string $text): string
    {
        return 'https://wa.me/'.self::phone().'?text='.urlencode($text);
    }

    public static function contactLink(string $message = 'Hola, quiero información sobre SaaS ERP'): string
    {
        return self::message($message);
    }

    public static function supportLink(): string
    {
        return self::message('Necesito ayuda con el sistema SaaS ERP');
    }
}
