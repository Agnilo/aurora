@props(['category' => null])

@php
    $categoryName = mb_strtolower($category?->name ?? '');
    $fallback = match (true) {
        str_contains($categoryName, 'emotion'), str_contains($categoryName, 'emoc') => 'heart',
        str_contains($categoryName, 'relation'), str_contains($categoryName, 'santyk') => 'users',
        str_contains($categoryName, 'career'), str_contains($categoryName, 'karjer') => 'briefcase-business',
        str_contains($categoryName, 'life'), str_contains($categoryName, 'gyven'),
        str_contains($categoryName, 'health') => 'leaf',
        default => 'book-open',
    };
@endphp

<x-stored-lucide :name="$category?->icon" :fallback="$fallback" {{ $attributes }} />
