<?php

return [
    'ai_provider' => env('CRM_AI_PROVIDER', 'internal'),
    'ai_api_key' => env('CRM_AI_API_KEY'),
    'ai_endpoint' => env('CRM_AI_ENDPOINT'),
    'ai_model' => env('CRM_AI_MODEL', 'gpt-4'),

    'umbral_vip' => 1000,
    'min_pedidos_frecuente' => 3,
    'dias_inactivo' => 30,
    'dias_riesgo' => 60,
];
