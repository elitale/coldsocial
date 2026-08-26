<?php

namespace App\Console\Commands;

use App\Models\AiProvider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('ai:provider:add {--name=} {--slug=} {--driver=} {--base-url=} {--key=} {--disabled : Create the provider disabled}')]
#[Description('Register an AI provider. The API key is stored encrypted and never displayed.')]
class AiProviderAddCommand extends Command
{
    public function handle(): int
    {
        $name = $this->option('name') ?: text('Provider name', required: true);
        $driver = $this->option('driver') ?: text('Driver key (e.g. openai, openrouter, github, anthropic)', required: true);
        $slug = Str::slug($this->option('slug') ?: $name);

        if (AiProvider::where('slug', $slug)->exists()) {
            $this->error("A provider with slug \"{$slug}\" already exists.");

            return self::FAILURE;
        }

        $key = $this->option('key') ?: password('API key (leave blank for none)');

        $provider = AiProvider::create([
            'name' => $name,
            'slug' => $slug,
            'driver' => $driver,
            'base_url' => $this->option('base-url') ?: null,
            'api_key' => $key ?: null,
            'enabled' => ! $this->option('disabled'),
        ]);

        $this->info("Provider \"{$provider->name}\" added (slug: {$provider->slug}, driver: {$provider->driver}).");

        return self::SUCCESS;
    }
}
