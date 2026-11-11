<?php
// Included only after index.php authentication.
$lv = $lang === 'lv';
?>
<section class="panel" id="freeStudio">
<h2><?= $lv ? 'Bezmaksas mūzikas un klipu darbnīca' : 'Free music and clip studio' ?></h2>
<p><?= $lv ? 'Veido oriģinālu instrumentālu fonu spēlēm un īsus klipus savā pārlūkā. Bez API atslēgām un augšupielādes. Šī ir algoritmiska kompozīcija un attēla animācija, nevis AI audio/video modelis.' : 'Create original instrumental game loops and short clips in your browser. No API keys or uploads. This is procedural composition and image animation, not an AI audio/video model.' ?></p>
<div class="form-grid">
<div><label for="fsMood"><?= $lv?'Noskaņa':'Mood' ?></label><select id="fsMood"><option value="calm"><?= $lv?'Mierīga':'Calm' ?></option><option value="arcade">Arcade</option><option value="night"><?= $lv?'Nakts':'Night' ?></option></select></div>
<div><label for="fsTempo">BPM</label><input id="fsTempo" type="number" min="50" max="160" value="96"></div>
<div><label for="fsSeed"><?= $lv?'Variācija (vienāds skaitlis = vienāda melodija)':'Variation (same number = same melody)' ?></label><input id="fsSeed" type="number" min="1" max="999999" value="63"></div>
<div><label for="fsSeconds"><?= $lv?'Ilgums sekundēs':'Duration in seconds' ?></label><select id="fsSeconds"><option>8</option><option selected>12</option><option>16</option></select></div>
</div>
<p><button type="button" class="primary" id="fsMusic"><?= $lv?'Veidot mūziku':'Create music' ?></button> <a id="fsWav" class="button ghost" hidden download="background.wav">WAV ↓</a></p>
<audio id="fsAudio" controls hidden></audio>
<hr>
<div class="form-grid">
<div><label for="fsImage"><?= $lv?'Klipam: attēls no ierīces (līdz 10 MB)':'For a clip: image from your device (up to 10 MB)' ?></label><input id="fsImage" type="file" accept="image/png,image/jpeg,image/webp"></div>
<div><label for="fsTitle"><?= $lv?'Teksts uz klipa':'Clip text' ?></label><input id="fsTitle" maxlength="90" placeholder="63.lv"></div>
</div>
<p><?= $lv?'Ģenerēto AI attēlu vispirms lejupielādē un izvēlies šeit. Izmanto savus vai atļautus attēlus; avota licences nosacījumi paliek spēkā. Eksports ilgst tikpat, cik klips. Turi cilni atvērtu un aktīvu.':'Download a generated AI image and select it here. Use your own or licensed images; source licence terms still apply. Export runs in real time. Keep this tab open and active.' ?></p>
<p><button type="button" class="button" id="fsVideo"><?= $lv?'Veidot klipu ar mūziku':'Create clip with music' ?></button> <button type="button" class="button ghost" id="fsStop" hidden><?= $lv?'Atcelt':'Cancel' ?></button> <a id="fsDownload" class="button ghost" hidden><?= $lv?'Lejupielādēt klipu':'Download clip' ?></a></p>
<p id="fsStatus" role="status" aria-live="polite"></p>
<canvas id="fsCanvas" width="960" height="540" hidden style="width:100%;max-width:960px"></canvas>
<video id="fsPreview" controls playsinline hidden style="width:100%;max-width:960px"></video>
</section>
