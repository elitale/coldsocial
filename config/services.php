<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT_URI'),
    ],

    'github_copilot' => [
        // Public OAuth client id for the VS Code-style device flow (not a secret). Override the
        // identity below if GitHub tightens verification against a specific editor build.
        'client_id' => env('GITHUB_COPILOT_CLIENT_ID', 'Iv1.b507a08c87ecfe98'),
        'editor_version' => env('GITHUB_COPILOT_EDITOR_VERSION', 'vscode/1.95.0'),
        'plugin_version' => env('GITHUB_COPILOT_PLUGIN_VERSION', 'copilot-chat/0.23.0'),
        'integration_id' => env('GITHUB_COPILOT_INTEGRATION_ID', 'vscode-chat'),
        'user_agent' => env('GITHUB_COPILOT_USER_AGENT', 'GitHubCopilotChat/0.23.0'),
    ],

];
