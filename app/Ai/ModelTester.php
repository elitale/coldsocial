<?php

namespace App\Ai;

use App\Enums\AiCapability;
use App\Models\AiModel;

class ModelTester
{
    /**
     * Capabilities we can exercise today via an OpenAI-compatible chat call.
     * Image / video / tts / stt gain testers as their drivers land (issues #37–#40).
     *
     * @var list<AiCapability>
     */
    private const CHAT_CAPABILITIES = [AiCapability::Text, AiCapability::Thinking];

    public function __construct(private readonly OpenAiCompatibleChat $chat) {}

    /**
     * Whether a live test is wired up for this model's capability.
     */
    public function supports(AiModel $model): bool
    {
        return in_array($model->capability, self::CHAT_CAPABILITIES, true);
    }

    /**
     * Run a minimal real request and return a short snippet of the model's reply.
     *
     * @throws ProviderRequestException
     */
    public function test(AiModel $model): string
    {
        return $this->chat->complete($model, 'Reply with a short, friendly one-line greeting.', maxTokens: 64);
    }
}
