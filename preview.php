<?php
declare(strict_types=1);
require __DIR__.'/includes/bootstrap.php';
aih_headers(false);aih_require_login();
$file=aih_safe_name((string)($_GET['file']??''));
if($file===''||strtolower(pathinfo($file,PATHINFO_EXTENSION))!=='html'){http_response_code(400);exit('Bad preview request');}
$path=AIH_GENERATED.'/'.$file;if(!is_file($path)){http_response_code(404);exit('Preview not found');}
if((int)(filesize($path)?:0)>5*1024*1024){http_response_code(413);exit('Preview too large');}
$html=(string)file_get_contents($path);$embed=!empty($_GET['embed']);
header("Content-Security-Policy: default-src 'none'; script-src 'unsafe-inline' https:; style-src 'unsafe-inline' https:; frame-src 'self' data: blob:; img-src data: https: blob:; font-src data: https:; connect-src https:; base-uri 'none'; form-action 'none'");
if($embed){header('Content-Type: text/html; charset=utf-8');echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>html,body,iframe{margin:0;width:100%;height:100%;border:0;background:#fff}body{overflow:hidden}</style></head><body><iframe sandbox="allow-scripts allow-forms allow-modals" srcdoc="'.htmlspecialchars($html,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'"></iframe></body></html>';exit;}
header('Content-Type: text/html; charset=utf-8');echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Prototype preview</title><style>*{box-sizing:border-box}html,body{margin:0;height:100%;font:14px system-ui;background:#eef2f7;color:#172033}.bar{height:52px;display:flex;align-items:center;justify-content:space-between;padding:0 14px;background:#fff;border-bottom:1px solid #dce4ee}.bar a{color:#315efb;text-decoration:none;font-weight:700}iframe{display:block;width:100%;height:calc(100% - 52px);border:0;background:#fff}</style></head><body><div class="bar"><strong>Free AI Hub 63 - Live prototype</strong><a href="download.php?bucket=generated&file='.rawurlencode($file).'">Download HTML</a></div><iframe sandbox="allow-scripts allow-forms allow-modals" srcdoc="'.htmlspecialchars($html,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'"></iframe></body></html>';
