<?php

namespace App\Contracts\AI;

interface AIProviderInterface
{
    public function chat(array $messages, array $options = []): string;
    public function parseReceipt(string $imageBase64, string $mimeType): array;
    public function generateInsight(array $financialContext): string;
    public function isAvailable(): bool;
    public function getProviderName(): string;
}
