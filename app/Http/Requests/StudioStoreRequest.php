<?php

namespace App\Http\Requests;

use App\Content\PlatformSpec;
use App\Enums\SocialPlatform;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudioStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:'.PlatformSpec::for(SocialPlatform::Linkedin)['charLimit']],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }
}
