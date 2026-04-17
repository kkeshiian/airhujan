<x-layouts.app :title="'Tambah Akun User'" :heading="'Tambah Akun User'" :subheading="'Buat akun baru untuk user/admin dari dashboard admin.'">
    <section class="max-w-2xl rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <form method="POST" action="{{ route('users.store') }}" class="grid gap-4 md:grid-cols-2">
            @csrf
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Role</label>
                <select name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Password</label>
                <input type="password" name="password" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button class="rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-white">Simpan</button>
                <a href="{{ route('users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Batal</a>
            </div>
        </form>
    </section>
</x-layouts.app>
