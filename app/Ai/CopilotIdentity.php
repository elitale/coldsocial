<?php

namespace App\Ai;

/**
 * Editor identity that GitHub expects on Copilot device-flow and API requests, mirroring VS Code.
 * Shared by {@see GithubDeviceFlow} and {@see CopilotToken}; the tunable values live in
 * config/services.php so they can be bumped without a code change.
 */
class CopilotIdentity
{
    public static function clientId(): string
    {
        return (string) config('services.github_copilot.client_id');
    }

    /**
     * Editor headers sent on the device-flow and token-exchange requests.
     *
     * @return array<string, string>
     */
    public static function headers(): array
    {
        return [
            'Editor-Version' => (string) config('services.github_copilot.editor_version'),
            'Editor-Plugin-Version' => (string) config('services.github_copilot.plugin_version'),
            'User-Agent' => (string) config('services.github_copilot.user_agent'),
        ];
    }

    /**
     * Headers for calls to the Copilot API itself (editor headers plus the integration id).
     *
     * @return array<string, string>
     */
    public static function apiHeaders(): array
    {
        return self::headers() + [
            'Copilot-Integration-Id' => (string) config('services.github_copilot.integration_id'),
        ];
    }
}
