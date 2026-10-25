<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:60'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'اسم المستخدم مطلوب.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $username = Str::lower(trim($this->string('username')->toString()));
        $user = User::query()->whereRaw('lower(username) = ?', [$username])->first();

        if ($user && $user->isLocked()) {
            throw ValidationException::withMessages([
                'username' => 'الحساب مقفول مؤقتاً بسبب محاولات فاشلة. حاول بعد قليل.',
            ]);
        }

        if (! $user || ! Auth::attempt([
            'username' => $user->username,
            'password' => $this->string('password')->toString(),
        ], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), 60);

            if ($user) {
                $user->registerFailedLogin();
            }

            throw ValidationException::withMessages([
                'username' => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
            ]);
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'username' => 'هذا الحساب غير مفعّل.',
            ]);
        }

        Auth::user()->clearLoginAttempts();
        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => "محاولات كثيرة. أعد المحاولة بعد {$seconds} ثانية.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }
}
