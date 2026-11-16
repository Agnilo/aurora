# Profilio dizaino atnaujinimas

Pritaikytas patvirtintas profilio maketas: kompaktiškas viršus be dekoratyvinio užrašo, nuotraukos redagavimo nuoroda, viešo profilio nuoroda, pastelinis progreso blokas ir vienodas formų stilius. Apdovanojimų skirtukas pašalintas, nes jis pasiekiamas bendrame sidebar.

Visos spalvos imamos iš esamo `resources/css/theme.css`. Keičiamas esamas `resources/css/frontend/pages/profile.css`; papildomo CSS failo nėra. Esamos profilio ir slaptažodžio formos, maršrutai bei serijos rodymo pasirinkimas išsaugoti. Nuotrauka keičiama ir pašalinama per esamą autentifikuotą avataro maršrutą. Senas failas pašalinamas po sėkmingo duomenų išsaugojimo.

## Pritaikymas

1. Sujungus šią šaką, atsinaujink vietinį projektą.
2. Importuok `resources/translations/profile_refresh_LT_EN.csv` per administravimo vertimų importą. Nauji raktai nesikerta su esamais; šablonuose yra LT / EN atsarginiai tekstai, todėl iki importo nėra rodomi neapdoroti raktai ar taškų pakaitalai.
3. Paleisk `npm run dev` arba `npm run build`.
4. Jei Blade šablonai buvo sukompiliuoti iš anksto, paleisk `php artisan view:clear`.

## Patikra

Šio pakeitimo metu vietinė vykdymo aplinka nepasiekiama. PHP testai, Vite build ir vizualinė patikra nebuvo paleisti.

Paleisk naujus nuotraukų testus su testine SQLite duomenų baze:

```bash
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --filter=ProfileAvatarTest
npm run build
```

Patikrink profilio duomenų išsaugojimą ir klaidas, slaptažodžio atnaujinimą, nuotraukos įkėlimą ir pašalinimą, viešo profilio nuorodą, ženklelių puslapį bei mobilų maketą. Naudotojui išjungus serijos rodymą, jos rodikliai ir atlygis turi likti paslėpti.
