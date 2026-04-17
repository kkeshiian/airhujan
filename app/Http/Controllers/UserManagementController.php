<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('role')->orderBy('name')->paginate(15);

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'user'])],
        ]);

        $payload['email'] = $this->buildSystemEmail($payload['username']);

        User::create($payload);

        return redirect()->route('users.index')->with('status', 'Akun user baru berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'password' => ['nullable', 'string', 'confirmed', 'min:8'],
        ]);

        $payload['email'] = $this->buildSystemEmail($payload['username'], $user->id);

        if (empty($payload['password'])) {
            unset($payload['password']);
        }

        $user->update($payload);

        return redirect()->route('users.index')->with('status', 'Data akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors([
                'user' => 'Admin tidak dapat menghapus akunnya sendiri.',
            ]);
        }

        $user->delete();

        return back()->with('status', 'Akun user berhasil dihapus.');
    }

    private function buildSystemEmail(string $username, ?int $ignoredUserId = null): string
    {
        $base = strtolower($username).'@monitoring.local';
        $email = $base;
        $counter = 1;

        while (
            User::query()
                ->when($ignoredUserId, fn ($query) => $query->where('id', '!=', $ignoredUserId))
                ->where('email', $email)
                ->exists()
        ) {
            $email = strtolower($username).'+'.$counter.'@monitoring.local';
            $counter++;
        }

        return $email;
    }
}
