<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LoginRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'email' => 'email|required',
            'password' => 'string|min:8|required',
        ];
    }

    /**
     * Get the user credentials.
     *
     * @return array<string>
     */
    public function credentials(): array
    {
        return $this->only('email', 'password');
    }

    public function rememberMe(): bool
    {
        return $this->boolean("remember");
    }

    private function failedLogin()
    {
        
    }

    /**
     * Authenticate the user.
     *
     * @return ?User
     */
    public function authenticate(): ?User
    {
        if (! Auth::attempt($this->credentials(), $this->rememberMe())) {
            $this->failedLogin();
            return null;
        }
        
        return User::with('teams')->find(Auth::id());
    }

    /**
     * Get the validated data.
     *
     * @return array<string>
     */
    public function validatedData(): array
    {
        return $this->only('email', 'password');
    }
}
