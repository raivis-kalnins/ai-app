<?php
declare(strict_types=1);

/** Self-hosted SVG flags: emoji flags render as plain country codes on some Windows browsers. */
function aih_locale_switcher(string $lang): string {
    $languages = aih_languages();
    if (!isset($languages[$lang])) $lang = 'en';
    $label = aih_t('language', $lang);
    $flag = static fn(string $code): string => 'assets/flags/'.rawurlencode($code).'.svg';
    $html = '<div class="locale-picker" data-locale-picker>';
    $html .= '<button class="locale-trigger" type="button" aria-haspopup="listbox" aria-expanded="false" aria-label="'.aih_e($label).': '.aih_e($languages[$lang]).'">';
    $html .= '<img class="locale-flag" src="'.aih_e($flag($lang)).'" alt="" width="22" height="16" loading="eager">';
    $html .= '<span class="locale-name">'.aih_e($languages[$lang]).'</span>';
    $html .= '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
    $html .= '<div class="locale-menu" role="listbox" aria-label="'.aih_e($label).'" hidden>';
    foreach ($languages as $code => $name) {
        $active = ($code === $lang);
        $html .= '<button type="button" role="option" class="locale-option'.($active?' is-active':'').'" data-language-value="'.aih_e($code).'" aria-selected="'.($active?'true':'false').'">';
        $html .= '<img class="locale-flag" src="'.aih_e($flag($code)).'" alt="" width="22" height="16" loading="lazy"><span>'.aih_e($name).'</span>';
        $html .= '<span class="locale-check" aria-hidden="true">'.($active?'✓':'').'</span></button>';
    }
    $html .= '</div><select id="languageSwitch" class="locale-native" tabindex="-1" aria-hidden="true">';
    foreach ($languages as $code => $name) $html .= '<option value="'.aih_e($code).'"'.($code === $lang ? ' selected' : '').'>'.aih_e($name).'</option>';
    return $html.'</select></div>';
}

function aih_theme_button(string $lang): string {
    return '<button id="themeToggle" class="button ghost compact theme-switch" type="button" aria-label="'.aih_e(aih_t('theme',$lang)).'" title="'.aih_e(aih_t('theme',$lang)).'">'
        .'<svg class="icon-moon" viewBox="0 0 24 24" width="19" height="19" aria-hidden="true"><path d="M20 15.2A8.3 8.3 0 0 1 8.8 4a8.5 8.5 0 1 0 11.2 11.2Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>'
        .'<svg class="icon-sun" viewBox="0 0 24 24" width="19" height="19" aria-hidden="true"><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42m0-14.14-1.42 1.42M6.35 17.65l-1.42 1.42" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>'
        .'</button>';
}

/** Small, localized sponsor/developer panel placed between content and footer. */
function aih_digitalpulse_cta(string $lang): string {
    $copy = [
        'en'=>['Websites, apps & AI that work harder for you','DigitalPulse builds modern websites, WordPress and WooCommerce shops, bespoke apps and smart AI-powered solutions.','Explore DigitalPulse'],
        'lv'=>['Mājaslapas, lietotnes un AI risinājumi jūsu izaugsmei','DigitalPulse veido modernas mājaslapas, WordPress un WooCommerce veikalus, pielāgotas lietotnes un gudrus AI risinājumus.','Apskatīt DigitalPulse'],
        'lt'=>['Svetainės, programėlės ir DI sprendimai jūsų augimui','DigitalPulse kuria modernias svetaines, WordPress ir WooCommerce parduotuves, individualias programėles ir DI sprendimus.','Atrasti DigitalPulse'],
        'et'=>['Veebilehed, rakendused ja AI-lahendused teie kasvuks','DigitalPulse loob kaasaegseid veebilehti, WordPressi ja WooCommerce’i poode, kohandatud rakendusi ning nutikaid AI-lahendusi.','Tutvu DigitalPulse’iga'],
        'sv'=>['Webbplatser, appar och AI för din tillväxt','DigitalPulse bygger moderna webbplatser, WordPress- och WooCommerce-butiker, skräddarsydda appar och smarta AI-lösningar.','Utforska DigitalPulse'],
        'no'=>['Nettsider, apper og KI for din vekst','DigitalPulse lager moderne nettsider, WordPress- og WooCommerce-butikker, skreddersydde apper og smarte KI-løsninger.','Utforsk DigitalPulse'],
        'da'=>['Websites, apps og AI til din vækst','DigitalPulse udvikler moderne websites, WordPress- og WooCommerce-butikker, specialbyggede apps og smarte AI-løsninger.','Udforsk DigitalPulse'],
        'fi'=>['Verkkosivut, sovellukset ja tekoäly kasvusi tueksi','DigitalPulse rakentaa moderneja verkkosivuja, WordPress- ja WooCommerce-kauppoja, räätälöityjä sovelluksia ja tekoälyratkaisuja.','Tutustu DigitalPulseen'],
        'is'=>['Vefsíður, öpp og gervigreind fyrir vöxt','DigitalPulse hannar nútímalegar vefsíður, WordPress- og WooCommerce-verslanir, sérsniðin öpp og snjallar gervigreindarlausnir.','Skoða DigitalPulse'],
        'pl'=>['Strony, aplikacje i AI dla rozwoju Twojej firmy','DigitalPulse tworzy nowoczesne strony, sklepy WordPress i WooCommerce, dedykowane aplikacje oraz inteligentne rozwiązania AI.','Poznaj DigitalPulse'],
        'ru'=>['Сайты, приложения и ИИ для вашего роста','DigitalPulse создаёт современные сайты, магазины WordPress и WooCommerce, приложения на заказ и интеллектуальные решения с ИИ.','Открыть DigitalPulse'],
        'uk'=>['Сайти, застосунки та ШІ для вашого розвитку','DigitalPulse створює сучасні сайти, магазини WordPress і WooCommerce, індивідуальні застосунки та розумні рішення зі ШІ.','Переглянути DigitalPulse'],
        'es'=>['Webs, aplicaciones e IA para impulsar tu negocio','DigitalPulse crea webs modernas, tiendas WordPress y WooCommerce, aplicaciones a medida y soluciones inteligentes de IA.','Descubre DigitalPulse'],
        'fr'=>['Sites web, applications et IA pour votre croissance','DigitalPulse crée des sites modernes, des boutiques WordPress et WooCommerce, des applications sur mesure et des solutions IA.','Découvrir DigitalPulse'],
        'de'=>['Websites, Apps und KI für Ihr Wachstum','DigitalPulse entwickelt moderne Websites, WordPress- und WooCommerce-Shops, individuelle Apps und intelligente KI-Lösungen.','DigitalPulse entdecken'],
    ];
    [$heading, $intro, $button] = $copy[$lang] ?? $copy['en'];
    $url = 'https://digitalpulse.click/';
    return '<aside class="dp-cta" aria-label="DigitalPulse">'
        .'<div class="dp-cta-copy"><span class="dp-eyebrow"><span class="dp-spark" aria-hidden="true">✦</span> DIGITALPULSE · WEB & AI DEVELOPMENT</span>'
        .'<h2>'.aih_e($heading).'</h2><p>'.aih_e($intro).'</p></div>'
        .'<a class="dp-cta-link" href="'.aih_e($url).'" target="_blank" rel="noopener noreferrer">'.aih_e($button).' <span aria-hidden="true">↗</span></a>'
        .'</aside>';
}
function aih_site_footer(string $lang): string {
    $credits = [
        'en'=>'Built by', 'lv'=>'Izstrādāja', 'lt'=>'Sukūrė', 'et'=>'Arendaja',
        'sv'=>'Skapad av', 'no'=>'Utviklet av', 'da'=>'Udviklet af',
        'fi'=>'Toteuttanut', 'is'=>'Hannað af', 'pl'=>'Stworzone przez',
        'ru'=>'Разработано', 'uk'=>'Розроблено', 'es'=>'Desarrollado por',
        'fr'=>'Créé par', 'de'=>'Entwickelt von'
    ];
    $label = $credits[$lang] ?? $credits['en'];
    $version = defined('AIH_VERSION') ? ' <small class="footer-version">v'.aih_e((string)AIH_VERSION).'</small>' : '';
    return '<footer class="aih-site-footer"><span>© '.date('Y').' Free AI Hub 63'.$version.'</span><span>'.aih_e($label).' <a href="https://digitalpulse.click/" target="_blank" rel="noopener noreferrer">DigitalPulse</a></span></footer>';
}
