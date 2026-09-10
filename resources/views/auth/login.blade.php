@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-700 text-xl font-bold text-white shadow-lg shadow-blue-700/30 dark:bg-blue-600">
            TU
        </div>
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ config('app.name', 'Sistem Informasi TU Sekolah') }}
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Masuk untuk mengelola data tata usaha sekolah</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        @session('status')
            <div class="mb-4 flex items-center rounded-lg border border-green-300 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-gray-800 dark:text-green-400" role="alert">
                {{ $value }}
            </div>
        @endsession

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-gray-800 dark:text-red-400" role="alert">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="admin@tu.com"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                >
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Kata Sandi</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                >
            </div>

            <div class="flex items-center">
                <input id="remember" type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 bg-gray-50 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600">
                <label for="remember" class="ms-2 select-none text-sm text-gray-600 dark:text-gray-300">Ingat saya</label>
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
            >
                Masuk
            </button>
        </form>
    </div>

    <p class="mt-6 text-center text-xs text-gray-400 dark:text-gray-500">
        &copy; {{ date('Y') }} {{ config('app.name', 'Sistem Informasi TU Sekolah') }}
    </p>
@endsection
