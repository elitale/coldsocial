<?php

namespace App\Http\Requests;

use App\Models\Persona;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class PersonaUpdateRequest extends FormRequest
{
    /**
     * Drop unknown / empty social links before validating.
     */
    protected function prepareForValidation(): void
    {
        $allowed = array_keys(Persona::options()['social_platforms']);

        $this->merge([
            'social_links' => array_filter(
                Arr::only((array) $this->input('social_links', []), $allowed),
                fn ($value): bool => filled($value),
            ),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $options = Persona::options();
        $in = fn (string $group) => Rule::in(array_keys($options[$group]));

        return [
            'primary_goal' => ['nullable', $in('primary_goal')],
            'headline' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', $in('industry')],
            'experience_level' => ['nullable', $in('experience_level')],
            'company' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'personality_archetype' => ['nullable', $in('personality_archetype')],
            'emoji_usage' => ['nullable', $in('emoji_usage')],
            'formality' => ['nullable', $in('formality')],
            'political_stance' => ['nullable', $in('political_stance')],
            'political_leaning' => ['nullable', $in('political_leaning')],
            'controversy_comfort' => ['nullable', $in('controversy_comfort')],
            'primary_platform' => ['nullable', $in('platforms')],
            'posting_frequency' => ['nullable', $in('posting_frequency')],
            'audience_note' => ['nullable', 'string', 'max:1000'],
            'dislikes' => ['nullable', 'string', 'max:1000'],
            'bio' => ['nullable', 'string', 'max:2000'],

            'languages' => ['nullable', 'array'],
            'languages.*' => [$in('languages')],
            'audiences' => ['nullable', 'array'],
            'audiences.*' => [$in('audiences')],
            'tones' => ['nullable', 'array'],
            'tones.*' => [$in('tones')],
            'interests' => ['nullable', 'array'],
            'interests.*' => [$in('interests')],
            'content_pillars' => ['nullable', 'array'],
            'content_pillars.*' => [$in('interests')],
            'likes' => ['nullable', 'array'],
            'likes.*' => [$in('likes')],
            'causes' => ['nullable', 'array'],
            'causes.*' => [$in('causes')],
            'content_formats' => ['nullable', 'array'],
            'content_formats.*' => [$in('content_formats')],
            'focus_platforms' => ['nullable', 'array'],
            'focus_platforms.*' => [$in('platforms')],

            'social_links' => ['nullable', 'array'],
            'social_links.*' => ['nullable', 'url', 'max:255'],
        ];
    }
}
