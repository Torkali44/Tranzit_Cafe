<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', [
            'user' => new User(['role' => UserRole::Cashier, 'is_active' => true]),
            'isAdminAccount' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedCashier($request);

        User::create([
            'name' => $data['name'],
            'username' => strtolower($data['username']),
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => UserRole::Cashier,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'تم إضافة الكاشير.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', [
            'user' => $user,
            'isAdminAccount' => $user->isProtectedAdmin(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        if ($user->isProtectedAdmin()) {
            $data = $this->validatedAdminProfile($request, $user);

            $user->name = $data['name'];
            $user->username = strtolower($data['username']);
            $user->email = $data['email'] ?? $user->email;
            // role stays admin — never changed
            $user->is_active = true;

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
                $user->clearLoginAttempts();
            }

            $user->save();

            return redirect()->route('admin.users.index')->with('success', 'تم تحديث حساب الإدارة.');
        }

        $data = $this->validatedCashier($request, $user);

        $user->name = $data['name'];
        $user->username = strtolower($data['username']);
        $user->email = $data['email'] ?? $user->email;
        $user->role = UserRole::Cashier;
        $user->is_active = $request->boolean('is_active');

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->clearLoginAttempts();
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'تم تحديث الكاشير.');
    }

    public function destroy(User $user)
    {
        if ($user->isProtectedAdmin()) {
            return back()->withErrors(['user' => 'لا يمكن حذف حساب الأدمن (الإدارة).']);
        }

        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'لا يمكن حذف حسابك الحالي.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'تم حذف الكاشير.');
    }

    private function validatedCashier(Request $request, ?User $user = null): array
    {
        $passwordRule = Password::min(10)->letters()->mixedCase()->numbers()->symbols();

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => [
                'required',
                'string',
                'min:4',
                'max:40',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($user?->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:120',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [$user ? 'nullable' : 'required', 'string', $passwordRule],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'username.regex' => 'اسم المستخدم: حروف إنجليزية وأرقام و _ . - فقط.',
            'password' => 'كلمة المرور 10 أحرف على الأقل مع حرف كبير وصغير ورقم ورمز.',
        ]);
    }

    private function validatedAdminProfile(Request $request, User $user): array
    {
        $passwordRule = Password::min(10)->letters()->mixedCase()->numbers()->symbols();

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => [
                'required',
                'string',
                'min:4',
                'max:40',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:120',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', $passwordRule],
        ], [
            'username.regex' => 'اسم المستخدم: حروف إنجليزية وأرقام و _ . - فقط.',
            'password' => 'كلمة المرور 10 أحرف على الأقل مع حرف كبير وصغير ورقم ورمز.',
        ]);
    }
}
