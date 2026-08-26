<?php

namespace App\Console\Commands;

use App\Ai\GithubDeviceFlow;
use App\Ai\ProviderRequestException;
use App\Models\AiProvider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('ai:provider:copilot {--name=GitHub Copilot : Display name for the provider} {--slug= : Override the generated slug}')]
#[Description('Sign in to GitHub Copilot with the VS Code-style device flow and register it as a provider.')]
class AiProviderCopilotCommand extends Command
{
    public function handle(GithubDeviceFlow $flow): int
    {
        $name = (string) ($this->option('name') ?: 'GitHub Copilot');
        $slug = Str::slug($this->option('slug') ?: $name);

        if (AiProvider::where('slug', $slug)->exists()) {
            $this->error("A provider with slug \"{$slug}\" already exists.");

            return self::FAILURE;
        }

        try {
            $device = $flow->requestCode();
        } catch (ProviderRequestException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Authorize coldsocial to use your GitHub Copilot subscription:');
        $this->line("  1. Open <options=bold>{$device['verification_uri']}</>");
        $this->line("  2. Enter the code: <options=bold>{$device['user_code']}</>");
        $this->newLine();
        $this->comment('Waiting for you to authorize in the browser…');

        try {
            $oauthToken = $flow->pollForToken($device['device_code'], $device['interval'], $device['expires_in']);
        } catch (ProviderRequestException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $provider = AiProvider::create([
            'name' => $name,
            'slug' => $slug,
            'driver' => 'copilot',
            'base_url' => null,
            'api_key' => $oauthToken,
            'enabled' => true,
        ]);

        $this->newLine();
        $this->info("✓ Signed in. Provider \"{$provider->name}\" added (slug: {$provider->slug}).");
        $this->line("  Add a model with: php artisan ai:model:add {$provider->slug}");

        return self::SUCCESS;
    }
}
