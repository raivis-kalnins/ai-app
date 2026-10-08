<?php
declare(strict_types=1);
require __DIR__.'/includes/bootstrap.php';require __DIR__.'/includes/pdf.php';
aih_headers(false);aih_require_login();
$id=preg_replace('/[^A-Za-z0-9_-]/','',(string)($_GET['id']??''));$format=(string)($_GET['format']??'pdf');$path=AIH_REPORTS.'/'.$id.'.json';if($id===''||!is_file($path)){http_response_code(404);exit('Report not found');}$r=aih_read_json($path,[]);if(!is_array($r)){http_response_code(500);exit('Bad report');}
if($format==='json'){header('Content-Type: application/json');header('Content-Disposition: attachment; filename="'.$id.'.json"');readfile($path);exit;}
if($format==='html'){
 header('Content-Type: text/html; charset=utf-8');
 echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Search report</title><style>body{font:14px system-ui;margin:0;background:#f3f6fb;color:#172033}.wrap{max-width:1100px;margin:30px auto;background:#fff;padding:30px;border-radius:18px}h1{margin-top:0}table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #e7ecf3;text-align:left;vertical-align:top}th{background:#eef3fa}a{color:#1d5fd1}small{color:#67738a}</style></head><body><div class="wrap"><h1>Search report</h1><small>'.aih_e((string)($r['created_at']??'')).'</small><table><thead><tr><th>#</th><th>Result</th><th>Price</th><th>Year</th><th>Mileage</th><th>Country</th><th>Source</th></tr></thead><tbody>';
 foreach(($r['results']??[])as$i=>$x)echo '<tr><td>'.($i+1).'</td><td><strong>'.aih_e((string)($x['title']??'')).'</strong><br><small>'.aih_e((string)($x['snippet']??'')).'</small></td><td>'.aih_e(trim((string)($x['price']??'').' '.(string)($x['currency']??''))).'</td><td>'.aih_e((string)($x['year']??'')).'</td><td>'.aih_e((string)($x['mileage']??'')).'</td><td>'.aih_e((string)($x['country']??'')).'</td><td><a href="'.aih_e((string)($x['url']??'')).'" rel="noreferrer">Open</a></td></tr>';
 echo '</tbody></table></div></body></html>';exit;
}
$pdf=aih_build_report_pdf($r);header('Content-Type: application/pdf');header('Content-Disposition: attachment; filename="'.$id.'.pdf"');header('Content-Length: '.strlen($pdf));echo $pdf;
