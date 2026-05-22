<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\Setting;

final class OpenAiHelper
{
    public static function chatCompletion(string $systemPrompt, string $userPrompt, array $options = []): array
    {
        $settings = new Setting();

        // Provider: OpenRouter (OpenAI-compatible endpoint).
        // Sesuai kebutuhan aplikasi TA: API key diambil dari tabel settings (key: openai_token).
        $apiKey = trim((string)$settings->get('openai_token', ''));
        if ($apiKey === '') {
            return [
                'ok' => false,
                'error' => "API key belum diset. Isi settings dengan key 'openai_token'.",
            ];
        }

        // Prioritas konfigurasi: $options (request) -> settings (DB) -> default config (constants).
        $model = trim((string)($options['model'] ?? $settings->get('openai_model', OPENAI_MODEL)));
        if ($model === '') {
            $model = OPENAI_MODEL;
        }
        if (!str_contains($model, '/')) {
            $model = 'openai/' . $model;
        }

        $temperatureRaw = (string)($options['temperature'] ?? $settings->get('openai_temperature', (string)OPENAI_TEMPERATURE));
        $temperature = is_numeric($temperatureRaw) ? (float)$temperatureRaw : (float)OPENAI_TEMPERATURE;
        if ($temperature < 0.0 || $temperature > 2.0) {
            $temperature = (float)OPENAI_TEMPERATURE;
        }

        $maxTokensRaw = (string)($options['max_tokens'] ?? $settings->get('openai_max_tokens', (string)OPENAI_MAX_TOKENS));
        $maxTokens = ctype_digit(trim($maxTokensRaw)) ? (int)$maxTokensRaw : (int)OPENAI_MAX_TOKENS;
        if ($maxTokens < 1) {
            $maxTokens = (int)OPENAI_MAX_TOKENS;
        }

        $payload = [
            'model' => $model,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ];

        if (!function_exists('curl_init')) {
            return [
                'ok' => false,
                'error' => 'PHP ext-curl belum aktif. Aktifkan extension curl di PHP (php.ini), lalu restart server.',
            ];
        }

        // Integrasi tanpa library tambahan: pakai cURL native PHP.
        $url = OPENAI_BASE_URL . '/chat/completions';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ];

        if (defined('OPENROUTER_HTTP_REFERER') && OPENROUTER_HTTP_REFERER !== '') {
            $headers[] = 'HTTP-Referer: ' . OPENROUTER_HTTP_REFERER;
        }
        if (defined('OPENROUTER_APP_TITLE') && OPENROUTER_APP_TITLE !== '') {
            $headers[] = 'X-Title: ' . OPENROUTER_APP_TITLE;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 60,
        ]);

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $errno) {
            return [
                'ok' => false,
                'error' => 'cURL error: ' . ($err ?: (string)$errno),
            ];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return [
                'ok' => false,
                'error' => 'Response AI tidak valid.',
                'raw' => $raw,
            ];
        }

        if ($status < 200 || $status >= 300) {
            $msg = $data['error']['message'] ?? ('HTTP ' . $status);
            return [
                'ok' => false,
                'error' => (string)$msg,
                'raw' => $data,
            ];
        }

        // Ambil teks jawaban asisten + info token usage (untuk indikator di UI).
        $content = (string)($data['choices'][0]['message']['content'] ?? '');
        $usage = $data['usage'] ?? [];

        return [
            'ok' => true,
            'content' => $content,
            'model' => (string)($data['model'] ?? $model),
            'usage' => [
                'input_tokens' => (int)($usage['prompt_tokens'] ?? 0),
                'output_tokens' => (int)($usage['completion_tokens'] ?? 0),
                'total_tokens' => (int)($usage['total_tokens'] ?? 0),
            ],
            'raw' => $data,
        ];
    }
}
