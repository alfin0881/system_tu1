@props(['color' => 'gray'])

@php
$colors = [
    'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'amber' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    'indigo' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'slate' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
];
$classes = $colors[$color] ?? $colors['gray'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$classes}"]) }}>
    {{ $slot }}
</span>
