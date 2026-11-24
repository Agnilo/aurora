@props(['value' => null])
<div {{ $attributes->class(['aurora-rich-content']) }}>{!! \App\Support\RichText::render($value) !!}</div>