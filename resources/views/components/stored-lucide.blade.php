@props(['name' => null, 'fallback' => 'circle'])

@php
    $aliases = [
        'people' => 'users', 'case' => 'briefcase-business',
        'book' => 'book-open', 'coin' => 'coins',
        '❤️' => 'heart', '❤' => 'heart', '💖' => 'heart', '🧠' => 'heart',
        '👥' => 'users', '🤝' => 'users', '💼' => 'briefcase-business',
        '🌿' => 'leaf', '🌱' => 'sprout', '🏃' => 'leaf', '💪' => 'leaf',
        '📚' => 'book-open', '📖' => 'book-open', '🪙' => 'coins', '💰' => 'coins',
        '🔥' => 'flame', '🎯' => 'target', '⭐' => 'star', '🌟' => 'star',
        '❗' => 'circle-alert', '🏅' => 'medal', '🏆' => 'trophy',
        '✨' => 'sparkles', '✓' => 'check', '✔' => 'check', '✅' => 'circle-check',
    ];
    $iconName = trim((string) $name);
    $iconName = $aliases[$iconName] ?? $iconName;
    $iconName = preg_replace('/^lucide-/', '', $iconName);
    if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $iconName)) {
        $iconName = $fallback;
    }

    $iconAttributes = $attributes->class(['aurora-icon'])->merge([
        'width' => 20,
        'height' => 20,
        'aria-hidden' => 'true',
        'focusable' => 'false',
    ])->getAttributes();

    try {
        $renderedIcon = svg('lucide-'.$iconName, '', $iconAttributes);
    } catch (\BladeUI\Icons\Exceptions\SvgNotFound $exception) {
        $renderedIcon = svg('lucide-'.$fallback, '', $iconAttributes);
    }
@endphp

{{ $renderedIcon }}
