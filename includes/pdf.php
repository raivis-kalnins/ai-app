<?php
declare(strict_types=1);

function aih_pdf_ascii(string $s): string {
    $s=str_replace(["\r","\n"],' ',$s);$x=@iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);return $x===false?$s:$x;
}
function aih_pdf_escape(string $s): string { return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],aih_pdf_ascii($s)); }
function aih_pdf_wrap(string $s,int $width=94): array { $s=preg_replace('/\s+/',' ',trim(aih_pdf_ascii($s)));return $s===''?['']:explode("\n",wordwrap($s,$width,"\n",true)); }

function aih_build_report_pdf(array $report): string {
    $pages=[];$lines=[];$push=function(string $text='',int $size=9,bool $bold=false)use(&$lines,&$pages){foreach(aih_pdf_wrap($text,$size>=15?70:94)as$l){$lines[]=[$l,$size,$bold];if(count($lines)>=48){$pages[]=$lines;$lines=[];}}};
    $push('Free AI Hub 63 - Search Report',18,true);$push('Generated: '.($report['created_at']??date(DATE_ATOM)),9,false);$in=$report['input']??[];$push('Mode: '.($in['mode']??'general').' | Query: '.($in['q']??trim(($in['make']??'').' '.($in['model']??''))),10,true);$push('',8,false);
    $results=$report['results']??[];$push('Results: '.count($results),12,true);
    foreach($results as$i=>$r){$push(($i+1).'. '.($r['title']??'Untitled'),11,true);$meta=[];foreach(['country','make','model','year','price','currency','mileage','location']as$k)if(!empty($r[$k]))$meta[]=$k.'='.$r[$k];if($meta)$push(implode(' | ',$meta),9,false);if(!empty($r['snippet']))$push((string)$r['snippet'],8,false);$push('Source: '.($r['url']??''),8,false);$push('',7,false);}if($lines)$pages[]=$lines;if(!$pages)$pages=[[['No results',12,true]]];
    $objects=[];$objects[1]='<< /Type /Catalog /Pages 2 0 R >>';$pageObjIds=[];$contentIds=[];$next=4;
    foreach($pages as$_){$pageObjIds[]=$next++;$contentIds[]=$next++;}$kids=implode(' ',array_map(fn($id)=>$id.' 0 R',$pageObjIds));$objects[2]='<< /Type /Pages /Kids [ '.$kids.' ] /Count '.count($pages).' >>';$objects[3]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
    foreach($pages as$pi=>$pl){$y=806;$stream="0.10 0.16 0.30 rg 36 786 523 34 re f\n1 1 1 rg BT /F1 16 Tf 48 797 Td (Free AI Hub 63) Tj ET\n";$y=770;foreach($pl as[$text,$size,$bold]){$rgb=$bold?'0.08 0.12 0.22':'0.18 0.20 0.25';$stream.="$rgb rg BT /F1 $size Tf 42 $y Td (".aih_pdf_escape($text).") Tj ET\n";$y-=($size+5);} $content=$stream;$cid=$contentIds[$pi];$pid=$pageObjIds[$pi];$objects[$cid]='<< /Length '.strlen($content).' >>' . "\nstream\n".$content."endstream";$objects[$pid]='<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents '.$cid.' 0 R >>';}
    ksort($objects);$pdf="%PDF-1.4\n";$offsets=[0=>0];foreach($objects as$id=>$obj){$offsets[$id]=strlen($pdf);$pdf.=$id." 0 obj\n".$obj."\nendobj\n";}$xref=strlen($pdf);$max=max(array_keys($objects));$pdf.="xref\n0 ".($max+1)."\n0000000000 65535 f \n";for($i=1;$i<=$max;$i++)$pdf.=sprintf('%010d 00000 n ',$offsets[$i]??0)."\n";$pdf.="trailer\n<< /Size ".($max+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";return $pdf;
}
