@props(['name', 'label' => null, 'type' => 'text', 'value' => null])

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            {{ $label }} @if ($attributes->get('required')) <span class="text-red-600 dark:text-red-500">*</span> @endif
        </label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500 dark:disabled:bg-gray-800 dark:disabled:text-gray-500']) }}
    >
    @error($name)
        <p class="mt-1.5 text-xs text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>
