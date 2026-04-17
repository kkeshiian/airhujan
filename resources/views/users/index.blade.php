<x-layouts.app :title="'Manajemen Akun User'" :heading="'Manajemen Akun User'" :subheading="'Admin dapat membuat, mengubah, dan menghapus akun user.'">
    <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-ink">Daftar Akun</h3>
            <a href="{{ route('users.create') }}" class="rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-white">Tambah User</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-3 py-2">Nama</th>
                        <th class="px-3 py-2">Username</th>
                        <th class="px-3 py-2">Role</th>
                        <th class="px-3 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2">{{ $user->name }}</td>
                            <td class="px-3 py-2">{{ $user->username ?? '-' }}</td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2 py-1 text-xs {{ $user->role === 'admin' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700' }}">{{ $user->role }}</span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="rounded-md border border-slate-300 px-2 py-1 text-xs">Edit</a>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus akun ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-red-300 px-2 py-1 text-xs text-red-700">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </section>
</x-layouts.app>
