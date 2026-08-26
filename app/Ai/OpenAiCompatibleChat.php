<?php

namespace App\Ai;

use App\Models\AiModel;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OpenAiCompatibleChat
{
    /**
     * Send a chat completion and return the assistant's reply.
     *
     * @throws ProviderRequestException
     */
    public function complete(AiModel $model, string $prompt, int $maxTokens = 256): string
    {
        $provider = $model->provider;
        $baseUrl = OpenAiCompatible::baseUrl($provider);

        try {
            $response = Http::withToken((string) $provider->api_key)
                ->acceptJson()
                ->timeout(30)
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model->identifier,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'max_tokens' => $maxTokens,
                ]);
        } catch (ConnectionException $e) {
            throw new ProviderRequestException("Could not reach {$baseUrl}.", previous: $e);
        }

        if (in_array($response->status(), [401, 403], true)) {
            throw new ProviderRequestException('The API key was rejected by the provider.');
        }

        if (! $response->successful()) {
            throw new ProviderRequestException("The provider returned HTTP {$response->status()}.");
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new ProviderRequestException('The model returned an empty response.');
        }

        return trim($content);
    }
}
