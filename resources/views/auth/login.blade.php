<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Monitoring Hujan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-cyan-100 via-white to-amber-100">
    <div class="mx-auto flex min-h-screen max-w-6xl items-center p-6">
        <div class="grid w-full gap-6 rounded-3xl bg-white/90 p-5 shadow-2xl shadow-cyan-200/40 lg:grid-cols-2 lg:p-10">
            <section class="rounded-2xl bg-slate-900 p-6 text-white lg:p-8">
                <p class="inline-flex rounded-full bg-cyan-400/30 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100">Monitoring Curah Hujan</p>
                <h1 class="mt-5 text-3xl font-semibold leading-tight">Satu layar untuk data sensor, status hujan, peta alat, dan rekaman audio.</h1>
                <ul class="mt-6 space-y-3 text-sm text-slate-200">
                    <li>Login menggunakan username dan password.</li>
                    <li>Tidak ada pendaftaran mandiri.</li>
                    <li>Akun user dikelola penuh oleh admin.</li>
                </ul>
            </section>

            <section class="rounded-2xl border border-slate-200 p-6 lg:p-8">
                <h2 class="text-2xl font-semibold text-slate-800">Masuk ke Sistem</h2>
                <p class="mt-1 text-sm text-slate-500">Gunakan akun yang sudah dibuat admin.</p>

                @if(session('status'))
                    <div class="mt-4 rounded-lg border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
                @endif

                @if($errors->any())
                    <div class="mt-4 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 focus:border-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                        <input type="password" name="password" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 focus:border-cyan-500 focus:outline-none">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>

                    <button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 font-semibold text-white hover:bg-slate-800">Login</button>
                </form>
            </section>
        </div>
    </div>
</body>
</html>
