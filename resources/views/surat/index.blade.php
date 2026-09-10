@extends('layouts.app')

@section('title', 'Daftar Surat')
@section('subtitle', 'Semua surat yang pernah dibuat')

@section('content')
@php $readonly = auth()->user()->role === 'kepsek'; @endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 flex-col gap-2 sm:max-w-2xl sm:flex-row">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari perihal atau nomor surat..."
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:text-gray-100 dark:placeholder:text-gray-500"
            >
            <select name="jenis_surat_id" onchange="this.form.submit()" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                <option value="">Semua Jenis</option>
                @foreach ($jenisSuratOptions as $j)
                    <option value="{{ $j->id }}" @selected(request('jenis_surat_id') == $j->id)>{{ $j->nama }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                <option value="">Semua Status</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="final" @selected(request('status') === 'final')>Final</option>
            </select>
            <button type="submit" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">Cari</button>
        </form>

        @unless ($readonly)
            <a href="{{ route('surat.create') }}">
                <x-button>+ Buat Surat</x-button>
            </a>
        @endunless
    </div>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nomor Surat</th>
                        <th class="px-5 py-3 font-medium">Perihal</th>
                        <th class="px-5 py-3 font-medium">Jenis</th>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($surat as $s)
                        <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900" onclick="window.location='{{ route('surat.show', $s) }}'">
                            <td class="px-5 py-3 font-medium text-blue-700 dark:text-blue-300">{{ $s->nomor_surat }}</td>
                            <td class="px-5 py-3 text-gray-900 dark:text-gray-100">
                                {{ $s->perihal }}
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->jenisSurat->nama ?? '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->tanggal_surat->translatedFormat('d F Y') }}</td>
                            <td class="px-5 py-3">
                                <x-badge :color="$s->status === 'final' ? 'green' : 'amber'">{{ ucfirst($s->status) }}</x-badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                Belum ada surat yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{ $surat->links() }}
</div>
@endsection
