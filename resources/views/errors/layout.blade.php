@php
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
    if ($code === '429' && isset($message) && is_string($message) && trim($message) !== '') {
        $description = $message;
    }
    $home = url('/' . $locale);
    $homeLabel = $locale === 'lt' ? 'Į pagrindinį' : 'Back to home';
    $tagline = $locale === 'lt' ? 'Maži darbai. Šviesesnės dienos.' : 'Small steps. Brighter days.';
    $logo = 'images/welcome/wordmark-1024-cropped.png';
    $hasLogo = is_file(public_path($logo));
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $code }} | {{ $heading }} | Aurora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --color-bg-page: #f8f7f2;
            --color-bg-card: #fffdfa;
            --color-ink: #28222c;
            --color-text-muted: #68616b;
            --color-pink: #f5c6cd;
            --color-blue: #cfe3f5;
            --color-mint: #cceada;
            --color-yellow: #ffdb86;
            --color-pink-deep: #ba7588;
            --color-rule: rgba(40, 34, 44, .18);
            --color-focus: #6953a4;
            --font-ui: 'DM Sans', system-ui, sans-serif;
            --font-mono: 'JetBrains Mono', ui-monospace, monospace;
            --font-brand: Georgia, 'Times New Roman', serif;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100svh;
            background-color: var(--color-bg-page);
            background-image: radial-gradient(circle, rgba(40, 34, 44, .24) .8px, transparent 1px);
            background-size: 17px 17px;
            color: var(--color-ink);
            font-family: var(--font-ui);
            -webkit-font-smoothing: antialiased;
        }

        .error-shell {
            min-height: 100vh;
            min-height: 100svh;
            display: flex;
            flex-direction: column;
        }

        .error-header,
        .error-footer {
            flex-shrink: 0;
            background: var(--color-bg-page);
        }

        .error-header {
            border-bottom: 1px solid var(--color-rule);
        }

        .error-header-inner,
        .error-footer-inner {
            width: min(calc(100% - 112px), 1380px);
            margin-inline: auto;
        }

        .error-header-inner {
            min-height: 90px;
            display: flex;
            align-items: center;
        }

        .error-wordmark,
        .error-footer-wordmark {
            display: inline-flex;
            align-items: center;
            color: var(--color-ink);
            font-family: var(--font-brand);
            text-decoration: none;
            letter-spacing: .23em;
        }

        .error-wordmark {
            font-size: 27px;
        }

        .error-logo {
            display: block;
            width: 154px;
            max-width: 100%;
            height: auto;
        }

        .error-main {
            flex: 1;
            display: grid;
            place-items: center;
            padding: 48px 24px;
            background:
                radial-gradient(ellipse at 30% 37%, rgba(245, 198, 205, .18), transparent 36%),
                radial-gradient(ellipse at 72% 63%, rgba(207, 227, 245, .22), transparent 36%);
        }

        .error-card {
            width: min(100%, 460px);
            min-width: 0;
            padding: 36px;
            border-radius: 22px;
            background: rgba(255, 253, 250, .88);
            box-shadow: 0 8px 28px rgba(40, 34, 44, .06);
        }

        .error-code {
            margin: 0 0 18px;
            font: 500 clamp(54px, 9vw, 78px)/1 var(--font-mono);
            letter-spacing: -.075em;
            color: var(--color-ink);
        }

        .error-card h1 {
            margin: 0;
            font-size: clamp(26px, 3vw, 34px);
            line-height: 1.16;
            letter-spacing: -.045em;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .error-description {
            margin: 14px 0 28px;
            color: var(--color-text-muted);
            font-size: 15px;
            line-height: 1.65;
            overflow-wrap: anywhere;
        }

        .error-button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 52px;
            width: 100%;
            padding: 14px 18px;
            border: 1px solid var(--color-ink);
            border-radius: 9px;
            background: var(--color-ink);
            color: #fff;
            box-shadow: 3px 4px 0 var(--color-pink-deep);
            font: 600 15px/1.5 var(--font-ui);
            text-decoration: none;
            transition: background-color .15s, transform .15s, box-shadow .15s;
        }

        .error-button:hover {
            background: #403641;
            transform: translate(1px, 1px);
            box-shadow: 2px 3px 0 var(--color-pink-deep);
        }

        .error-button:active {
            transform: translate(3px, 4px);
            box-shadow: none;
        }

        .error-button svg {
            display: block;
            flex: 0 0 20px;
        }

        a:focus-visible {
            outline: 2px solid var(--color-focus);
            outline-offset: 4px;
        }

        .error-footer {
            border-top: 1px solid var(--color-rule);
        }

        .error-footer-inner {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 16px;
            min-height: 66px;
            padding: 16px 0;
            color: var(--color-text-muted);
            font: 400 11px/1.7 var(--font-mono);
        }

        .error-footer-wordmark {
            justify-self: end;
            font-size: 18px;
        }

        .error-footer-logo {
            display: block;
            width: 98px;
            height: auto;
        }

        @media (max-width: 760px) {

            .error-header-inner,
            .error-footer-inner {
                width: calc(100% - 48px);
            }

            .error-header-inner {
                min-height: 76px;
            }
        }

        @media (max-width: 520px) {

            .error-header-inner,
            .error-footer-inner {
                width: calc(100% - 36px);
            }

            .error-main {
                padding: 32px 18px;
            }

            .error-card {
                padding: 28px 24px;
                border-radius: 16px;
            }

            .error-logo {
                width: 125px;
            }

            .error-footer-inner {
                grid-template-columns: auto 1fr;
                font-size: 10px;
            }

            .error-footer-tagline {
                grid-column: 1 / -1;
                grid-row: 2;
            }

            .error-footer-year {
                grid-column: 1;
                grid-row: 1;
            }

            .error-footer-wordmark {
                grid-column: 2;
                grid-row: 1;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .error-button {
                transition: none;
            }
        }
    </style>
</head>

<body>
    <div class="error-shell">
        <header class="error-header">
            <div class="error-header-inner">
                <a class="error-wordmark" href="{{ $home }}" aria-label="Aurora">
                    @if ($hasLogo)
                        <img class="error-logo" src="{{ asset($logo) }}" alt="Aurora">
                    @else
                        AURORA
                    @endif
                </a>
            </div>
        </header>
        <main class="error-main" id="main">
            <section class="error-card" aria-labelledby="error-title">
                <p class="error-code">{{ $code }}</p>
                <h1 id="error-title">{{ $heading }}</h1>
                <p class="error-description">{{ $description }}</p>
                <a class="error-button" href="{{ $home }}">
                    {{ $homeLabel }}
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true" focusable="false">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </a>
            </section>
        </main>
        <footer class="error-footer">
            <div class="error-footer-inner">
                <span class="error-footer-tagline">{{ $tagline }}</span>
                <span class="error-footer-year">© {{ date('Y') }}</span>
                <span class="error-footer-wordmark" aria-hidden="true">
                    @if ($hasLogo)
                        <img class="error-footer-logo" src="{{ asset($logo) }}" alt="">
                    @else
                        AURORA
                    @endif
                </span>
            </div>
        </footer>
    </div>
</body>

</html>