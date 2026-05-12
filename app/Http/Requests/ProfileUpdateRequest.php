<?php

namespace App\Http\Requests;

use App\Models\Member; // 1. Use Member instead of User
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    // app/Http/Requests/ProfileUpdateRequest.php

public function rules(): array
{
    return [
        'username' => [
            'required', 'string', 'lowercase', 'max:50',
            Rule::unique('members', 'username')->ignore($this->user()->userId, 'userId'),
        ],
        'email' => [
            'required', 'string', 'lowercase', 'email', 'max:100',
            Rule::unique('members', 'email')->ignore($this->user()->userId, 'userId'),
        ],
        // Allow these to be images
        'profile_image' => ['nullable', 'image', 'max:2048'], // Max 2MB
        'background_image' => ['nullable', 'image', 'max:5120'], // Max 5MB
    ];
}
}
