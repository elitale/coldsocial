<?php

namespace App\Http\Requests\Settings;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PostingUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'timezone' => ['required', 'string', function (string $attribute, mixed $value, Closure $fail): void {
                try {
                    new \DateTimeZone(is_string($value) ? $value : '');
                } catch (\Exception) {
                    $fail('The selected timezone is invalid.');
                }
            }],
        ];
    }
}
