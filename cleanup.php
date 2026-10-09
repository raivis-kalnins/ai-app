<?php
declare(strict_types=1);
define('AIH_SKIP_AUTO_CLEANUP', true);
require __DIR__.'/includes/bootstrap.php';
if(PHP_SAPI!=='cli'){aih_headers(false);aih_require_login();aih_check_csrf($_POST['csrf']??null);}
$r=aih_cleanup_expired(true);if(PHP_SAPI==='cli'){echo json_encode($r,JSON_PRETTY_PRINT).PHP_EOL;exit;}aih_json(['ok'=>true]+$r);
