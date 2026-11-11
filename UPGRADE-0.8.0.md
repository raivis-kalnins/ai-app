# ai.63.lv — 0.8.0 aizvietojamie faili

Paredzēts esošam `ai-app` no šajā repozitorijā esošās 0.7.3 versijas. Nav vajadzīgs Node.js, Composer, GPU vai datubāzes migrācija uz cPanel servera.

1. cPanel File Manager atver tieši **ai.63.lv dokumentu sakni**. Izveido esošo failu rezerves kopiju.
2. Izpako `ai-app-0.8.0-replace.zip` šajā saknē un aizvieto failus, saglabājot mapju struktūru. `index.php` jāatrodas tieši saknē, nevis jaunā `ai-app` apakšmapē.
3. **Nedzēs `storage/` un `config.local.php`.** ZIP nesatur tavus čatus, augšupielādes vai servera atslēgas. Esošā derīgā īpašnieka parole paliek spēkā.
4. Lieto PHP 8+ ar cURL un mbstring. Saglabā esošās drošās failu atļaujas (parasti faili 0644, mapes 0755).
5. Pārlādē lapu ar Ctrl+Shift+R; aizver un atver instalēto PWA, ja redzama vecā versija. Lapas paziņojumā jāparādās bezmaksas cPanel režīmam.
6. Pārbaudi vienu attēlu ar `Auto` vai `AI Horde`. Gaidīšanas laikā var atcelt uzdevumu. Atkārtots tā paša pieprasījuma sūtījums tajā pašā cilnē turpina saglabātā uzdevuma statusa pārbaudi. Citā cilnē šī atjaunošana nav pieejama.
7. Media sadaļas sākumā izmēģini mūziku → WAV. Izvēlies savu PNG/JPEG/WebP un tekstu → klips ar mūziku. Eksports ilgst 8–16 sekundes; turi cilni aktīvu. Formāts ir WebM vai MP4 atkarībā no pārlūka. Ja pārlūks neatbalsta eksportu, mūzikas WAV joprojām var izveidot.

## Kas ir bez maksas un kādi ir ierobežojumi

- AI Horde ir kopienas pakalpojums: rindas, modeļu pieejamība un rezultātu kvalitāte nav garantēta. Attēlu apraksti tiek nosūtīti kopienas pakalpojumam. Nesūti konfidenciālu informāciju.
- Browser AI/WebLLM izmanto apmeklētāja ierīci un lejupielādē modeli; nepieciešams piemērots WebGPU pārlūks. Tā nav attēlu ģenerēšana.
- Mūzika šajā laidienā ir algoritmiska instrumentāla kompozīcija, nevis AI radīta dziesma ar vokālu. Klips ir attēla kustība, teksts un mūzika, nevis AI radīta video aina.
- Neviens jauns maksas API nav pieslēgts. Mākoņu kredītu pakalpojumi, tostarp Puter un Pollinations, ir bloķēti arī ar saglabātām atslēgām. Iestatījumos redzamie vēsturiskie API lauki nepiešķir piekļuvi maksas ceļiem.
- Attēlu meklēšana ar `Free web images` saglabāta atsevišķi; materiālu licences un avoti jāpatur. Esošie krājumu attēli netiek pasniegti kā AI ģenerēti.
- Maksā esošā hostinga plāna izmaksas; šis atjauninājums neprasa jaunu abonementu. Nav solīts neierobežots vai vienmēr pieejams bezmaksas mākonis.
- FirstHost norāda Profesionālā plāna viena konta limitus 4 GB RAM / 2 CPU / 100 EP. Neuzstādi SD, ComfyUI vai video modeļus šajā kontā. Ja vēlāk ir atsevišķs dators ar GPU, atbalsts ārējam pašmitinātam serverim ir saglabāts.

## Atgriešanās pie iepriekšējās versijas

Atjauno aizvietotos failus no savas rezerves kopijas un veic pilnu pārlādi. Neatjauno vecāku `storage/` kopiju pāri jaunajiem čatiem bez vajadzības. Šis laidiena ZIP netiek automātiski izvietots serverī.

## Pārbaudes

Visu PHP failu lint (PHP WebAssembly CLI), visu JavaScript failu sintakse, esošie auth/UI testi, jaunie bezmaksas maršrutēšanas un WAV testi. Pārlūka eksporta pārbaudes aprakstītas `TEST-RESULTS-0.8.0.md`. Dzīvais FirstHost konts un reāla AI Horde ģenerēšana nav pārbaudīti; pārbaudi pēc uzstādīšanas.
