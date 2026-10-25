@php
    // Error rendering must not depend on database translations, Vite or authentication.
    $segment = request()->segment(1);
    $locale = in_array($segment, ['lt', 'en'], true)
        ? $segment
        : (in_array(app()->getLocale(), ['lt', 'en'], true) ? app()->getLocale() : 'lt');
    $messages = [
        'lt' => [
            '404' => ['Puslapis nerastas.', 'Panašu, kad šis kelias niekur neveda. Grįžkime į pradžią.'],
            '403' => ['Šis puslapis nepasiekiamas.', 'Tavo paskyra neturi leidimo peržiūrėti šio puslapio.'],
            '419' => ['Sesija pasibaigė.', 'Atidaryk formą iš naujo ir bandyk dar kartą.'],
            '429' => ['Truputį per greitai.', 'Gavome per daug užklausų. Šiek tiek palauk ir bandyk dar kartą.'],
            '500' => ['Kažkas nepavyko.', 'Įvyko serverio klaida. Pabandyk sugrįžti šiek tiek vėliau.'],
            '503' => ['Trumpa pertrauka.', 'Aurora šiuo metu nepasiekiama. Pabandyk sugrįžti vėliau.'],
        ],
        'en' => [
            '404' => ['Page not found.', 'Looks like this path leads nowhere. Let’s head back to the start.'],
            '403' => ['This page is unavailable.', 'Your account does not have permission to view this page.'],
            '419' => ['Your session has expired.', 'Open the form again and give it another try.'],
            '429' => ['A little too fast.', 'We received too many requests. Wait a little and try again.'],
            '500' => ['Something went wrong.', 'A server error occurred. Please try again later.'],
            '503' => ['Taking a short break.', 'Aurora is currently unavailable. Please come back later.'],
        ],
    ];
    $code = trim($__env->yieldContent('code', '500'));
    [$heading, $description] = $messages[$locale][$code] ?? $messages[$locale]['500'];
    $home = url('/'.$locale);
    $homeLabel = $locale === 'lt' ? 'Į pagrindinį' : 'Back to home';
    $background = 'images/welcome/eveling-salazar-5uu5Sa--Pcw-unsplash.jpg';
    if (!file_exists(public_path($background))) {
        $background = 'images/welcome/background.webp';
    }
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $code }} · {{ $heading }} · Aurora</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100svh;
            color: #10151e;
            font-family: 'DM Sans', Arial, sans-serif;
            background: #eeeae6;
            isolation: isolate;
        }

        .error-background,
        body::before {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .error-background {
            z-index: -2;
            object-fit: cover;
            filter: saturate(0.85);
        }

        body::before {
            content: '';
            z-index: -1;
            background: linear-gradient(90deg, rgba(250, 247, 243, 0.75), rgba(250, 247, 243, 0.45));
        }

        .error-shell {
            width: min(calc(100% - 80px), 1370px);
            min-height: 100svh;
            margin-inline: auto;
            display: flex;
            flex-direction: column;
        }

        header {
            min-height: 80px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(25, 30, 40, 0.18);
        }

        .error-wordmark {
            color: inherit;
            font-family: Georgia, serif;
            font-size: 27px;
            letter-spacing: 0.23em;
            text-decoration: none;
        }

        main {
            flex: 1;
            display: grid;
            place-items: center;
            padding: 64px 0;
        }

        .error-card {
            width: min(100%, 620px);
            padding: clamp(28px, 5vw, 56px);
            border: 1px solid rgba(255, 255, 255, 0.65);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.28);
            -webkit-backdrop-filter: blur(20px);
            backdrop-filter: blur(20px);
            box-shadow: 0 12px 32px rgba(30, 26, 29, 0.06);
        }

        .error-code {
            margin: 0 0 16px;
            font-family: 'JetBrains Mono', monospace;
            font-size: clamp(72px, 12vw, 120px);
            line-height: 1;
            letter-spacing: -0.07em;
        }

        h1 {
            margin: 0;
            font-size: clamp(26px, 4vw, 36px);
            line-height: 1.15;
            letter-spacing: -0.04em;
        }

        .error-description {
            margin: 18px 0 28px;
            font-size: 17px;
            line-height: 1.6;
        }

        .error-button {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            min-height: 50px;
            padding: 13px 24px;
            border-radius: 6px;
            background: #191a1b;
            color: #fff;
            font-weight: 500;
            text-decoration: none;
        }

        .error-button:hover {
            background: #303136;
        }

        a:focus-visible {
            outline: 2px solid #9b4f61;
            outline-offset: 5px;
        }

        footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;

            padding: 20px 0;
            border-top: 1px solid rgba(25, 30, 40, 0.18);

            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
        }

        .error-logo {
            display: block;
            width: 160px;
            height: auto;
        }

        .error-footer-logo {
            display: block;
            width: 100px;
            height: auto;
        }

        @media (max-width: 520px) {
            .error-shell {
                width: calc(100% - 32px);
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=JetBrains+Mono&display=swap" rel="stylesheet">
</head>
<body>
    @if (file_exists(public_path($background)))
        <img class="error-background" src="{{ asset($background) }}" alt="">
    @endif
    <div class="error-shell">
        <header>
            <a class="error-wordmark" href="{{ $home }}">
                <img
                    class="error-logo"
                    src="{{ asset('images/welcome/wordmark-1024-cropped.png') }}"
                    alt="Aurora"
                >
            </a>
        </header>
        <main>
            <section class="error-card" aria-labelledby="error-title">
                <p class="error-code">{{ $code }}</p>
                <h1 id="error-title">{{ $heading }}</h1>
                <p class="error-description">{{ $description }}</p>
                <a class="error-button" href="{{ $home }}">
                    {{ $homeLabel }}
                    <span aria-hidden="true"><x-lucide-arrow-right class="aurora-icon" width="20" height="20" aria-hidden="true" focusable="false" /></span>
                </a>
            </section>
        </main>
        <footer>
            <span>
                {{ $locale === 'lt'
                    ? 'Maži darbai. Šviesesnės dienos.'
                    : 'Small steps. Brighter days.' }}
            </span>

            <img
                class="error-footer-logo"
                src="{{ asset('images/welcome/wordmark-1024-cropped.png') }}"
                alt=""
                aria-hidden="true"
            >
        </footer>
    </div>
</body>
</html>
