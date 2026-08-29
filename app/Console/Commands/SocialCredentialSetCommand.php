<?php

namespace App\Console\Commands;

use App\Enums\SocialPlatform;
use App\Models\PlatformCredential;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[Signature('social:credential:set {--platform=} {--client-id=} {--client-secret=} {--redirect=} {--test : Test the credentials right after saving}')]
#[Description('Store a social platform OAuth app client id and secret (secret encrypted at rest).')]
class SocialCredentialSetCommand extends Command
{
    public function handle(): int
    {
        $platform = $this->resolvePlatform();

        if (! $platform instanceof SocialPlatform) {
            return self::FAILURE;
        }

        $clientId = (string) ($this->option('client-id') ?: text('Client ID', required: true));

        $clientSecret = $this->option('client-secret');

        if ($clientSecret === null && $this->input->isInteractive()) {
            $clientSecret = password('Client secret', required: true);
        }

        $clientSecret = (string) $clientSecret;

        if ($clientId === '' || $clientSecret === '') {
            $this->error('Both a client id and client secret are required.');

            return self::FAILURE;
        }

        $redirect = (string) ($this->option('redirect') ?: route('connections.callback', ['platform' => $platform->value]));

        $credential = PlatformCredential::updateOrCreate(
            ['platform' => $platform],
            [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_url' => $redirect,
            ],
        );

        $this->info("Saved {$platform->label()} credentials — the client secret is stored encrypted.");
        $this->line('Register this redirect URL with the provider:');
        $this->line("  {$credential->redirect_url}");

        if ($this->option('test')) {
            $this->newLine();

            return $this->call('social:credential:test', ['platform' => $platform->value]);
        }

        return self::SUCCESS;
    }

    private function resolvePlatform(): ?SocialPlatform
    {
        $connectable = array_values(array_filter(
            SocialPlatform::cases(),
            fn (SocialPlatform $platform): bool => $platform->connectable(),
        ));

        $value = $this->option('platform');

        if (is_string($value) && $value !== '') {
            $platform = SocialPlatform::tryFrom($value);

            if (! $platform instanceof SocialPlatform || ! $platform->connectable()) {
                $this->error("\"{$value}\" is not a connectable platform.");

                return null;
            }

            return $platform;
        }

        if (count($connectable) === 1) {
            return $connectable[0];
        }

        $choice = select('Platform', array_combine(
            array_map(fn (SocialPlatform $platform): string => $platform->value, $connectable),
            array_map(fn (SocialPlatform $platform): string => $platform->label(), $connectable),
        ));

        return SocialPlatform::from($choice);
    }
}
