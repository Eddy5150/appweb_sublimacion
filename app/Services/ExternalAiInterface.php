<?php

namespace App\Services;

use App\Models\Cliente;

abstract class ExternalAiInterface
{
    protected array $config = [];

    public function __construct()
    {
        $this->config = [
            'provider' => config('crm.ai_provider', 'internal'),
            'api_key' => config('crm.ai_api_key'),
            'endpoint' => config('crm.ai_endpoint'),
            'model' => config('crm.ai_model', 'gpt-4'),
        ];
    }

    abstract public function predictNextPurchase(Cliente $cliente): array;

    abstract public function generateRecommendations(Cliente $cliente): array;

    abstract public function analyzeSentiment(string $text): array;

    public function isConnected(): bool
    {
        return $this->config['provider'] !== 'internal'
            && ! empty($this->config['api_key'])
            && ! empty($this->config['endpoint']);
    }
}
