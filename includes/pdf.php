<?php
declare(strict_types=1);

function aih_pdf_ascii(string $s): string {
    $s=str_replace(["\r","\n"],' ',$s);
    $x=@iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);
    return $x===false?$s:$x;
}
function aih_pdf_escape(string $s): string { return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],aih_pdf_ascii($s)); }
function aih_pdf_wrap(string $s,int $width=88): array {
    $s=preg_replace('/\s+/',' ',trim(aih_pdf_ascii($s)));
    return $s===''?['']:explode("\n",wordwrap($s,max(20,$width),"\n",true));
}
function aih_pdf_rgb(string $hex): string {
    $hex=ltrim($hex,'#');if(strlen($hex)!==6)return '0 0 0';
    $r=hexdec(substr($hex,0,2))/255;$g=hexdec(substr($hex,2,2))/255;$b=hexdec(substr($hex,4,2))/255;
    return sprintf('%.3f %.3f %.3f',$r,$g,$b);
}
function aih_pdf_text(float $x,float $y,string $text,float $size=9,string $font='F1',string $color='#172033'): string {
    return aih_pdf_rgb($color)." rg BT /$font $size Tf $x $y Td (".aih_pdf_escape($text).") Tj ET\n";
}
function aih_pdf_rect(float $x,float $y,float $w,float $h,string $fill='#FFFFFF',?string $stroke=null,float $line=0.7): string {
    $cmd=aih_pdf_rgb($fill)." rg $x $y $w $h re f\n";
    if($stroke!==null)$cmd.=aih_pdf_rgb($stroke)." RG $line w $x $y $w $h re S\n";
    return $cmd;
}
function aih_pdf_sector_label(string $sector): string {
    return match($sector){
        'vehicles'=>'Vehicles','real_estate'=>'Real estate','jobs'=>'Jobs','services'=>'Services','digital_agency'=>'Web / digital agencies',
        'electronics'=>'Electronics','computers'=>'Computers / IT','phones'=>'Phones / tablets','home_garden'=>'Home / garden','construction'=>'Construction',
        'agriculture'=>'Agriculture','transport'=>'Transport / logistics','animals'=>'Animals / pets','fashion'=>'Clothing / fashion','business'=>'Business / industrial',
        'leisure'=>'Sports / leisure','education'=>'Education / training','health_beauty'=>'Health / beauty','other'=>'Other classifieds',default=>'All sectors'
    };
}
function aih_pdf_trim_url(string $url,int $max=115): string {
    $url=trim($url);return strlen($url)>$max?substr($url,0,$max-3).'...':$url;
}

function aih_build_report_pdf(array $report): string {
    $pages=[];$stream='';$y=742;$pageNo=0;
    $newPage=function()use(&$pages,&$stream,&$y,&$pageNo){
        if($stream!=='')$pages[]=$stream;
        $pageNo++;
        $stream="1 1 1 rg 0 0 595 842 re f\n";
        $stream.=aih_pdf_rect(0,770,595,72,'#0F1F46');
        $stream.=aih_pdf_rect(36,792,34,34,'#4F6FFF');
        $stream.=aih_pdf_text(45,803,'AI',12,'F2','#FFFFFF');
        $stream.=aih_pdf_text(82,807,'Free AI Hub 63',18,'F2','#FFFFFF');
        $stream.=aih_pdf_text(82,790,'Smart Search report',9,'F1','#D9E4FF');
        $y=742;
    };
    $need=function(float $h)use(&$y,$newPage){if($y-$h<52)$newPage();};
    $section=function(string $title)use(&$stream,&$y,$need){$need(28);$stream.=aih_pdf_text(36,$y,$title,12,'F2','#173B87');$stream.=aih_pdf_rect(36,$y-8,523,1.2,'#DCE6F8');$y-=25;};
    $paragraph=function(string $text,int $size=9,string $color='#334155',int $width=90,string $font='F1')use(&$stream,&$y,$need){$lines=aih_pdf_wrap($text,$width);foreach($lines as$l){$need($size+6);$stream.=aih_pdf_text(44,$y,$l,$size,$font,$color);$y-=($size+5);}return count($lines);};
    $card=function(string $title,string $text,string $fill,string $accent)use(&$stream,&$y,$need){
        $titleLines=aih_pdf_wrap($title,72);$textLines=aih_pdf_wrap($text,88);$h=20+count($titleLines)*12+count($textLines)*11+16;$need($h+8);
        $stream.=aih_pdf_rect(36,$y-$h+7,523,$h,$fill,'#DFE6F1',0.6);$stream.=aih_pdf_rect(36,$y-$h+7,4,$h,$accent);
        $yy=$y-12;foreach($titleLines as$l){$stream.=aih_pdf_text(50,$yy,$l,10,'F2','#172033');$yy-=12;}foreach($textLines as$l){$stream.=aih_pdf_text(50,$yy,$l,8.5,'F1','#42516A');$yy-=11;}$y-=$h+10;
    };

    $newPage();
    $in=is_array($report['input']??null)?$report['input']:[];
    $query=trim((string)($in['q']??''));if($query==='')$query=trim((string)($in['brand']??$in['make']??'').' '.(string)($in['model']??''));
    $countries=$in['countries']??['ALL'];if(!is_array($countries))$countries=[$countries];
    $sectors=$in['sectors']??['ALL'];if(!is_array($sectors))$sectors=[$sectors];
    $sectorLabels=array_map(fn($x)=>aih_pdf_sector_label((string)$x),$sectors);
    $engines=array_values(array_unique(array_map('strval',(array)($report['engines']??[]))));
    $created=(string)($report['created_at']??date(DATE_ATOM));

    $metaLines=[];
    foreach(aih_pdf_wrap('Query: '.($query!==''?$query:'(structured search)'),78) as$l)$metaLines[]=$l;
    $metaLines[]='Countries: '.implode(', ',$countries);
    $metaLines[]='Sectors: '.implode(', ',$sectorLabels);
    $metaLines[]='Search resources: '.($engines?implode(', ',$engines):'available configured resources');
    $metaH=24+count($metaLines)*13;
    $need($metaH);
    $stream.=aih_pdf_rect(36,$y-$metaH+8,523,$metaH,'#F6F9FF','#DCE6F8',0.7);
    $stream.=aih_pdf_text(50,$y-10,'Search overview',11,'F2','#173B87');$yy=$y-28;
    foreach($metaLines as$l){$stream.=aih_pdf_text(50,$yy,$l,8.7,'F1','#334155');$yy-=13;}$y-=$metaH+8;
    $stream.=aih_pdf_text(36,$y,'Generated '.$created,7.7,'F3','#7A879C');$y-=22;

    $filterMap=is_array($in['sector_filters']??null)?$in['sector_filters']:[];
    if($filterMap){$filterParts=[];foreach($filterMap as$sec=>$vals){if(!is_array($vals))continue;$items=[];foreach($vals as$k=>$v){$v=trim((string)$v);if($v!=='')$items[]=ucwords(str_replace('_',' ',(string)$k)).': '.$v;}if($items)$filterParts[]=aih_pdf_sector_label((string)$sec).' - '.implode(', ',$items);}if($filterParts)$card('Structured filters',implode("
",$filterParts),'#F8FAFC','#21A1A1');}

    if(!empty($report['ai_summary']['text'])){
        $card('AI comparison summary - '.(string)($report['ai_summary']['provider']??'AI'),(string)$report['ai_summary']['text'],'#EEF4FF','#4F6FFF');
    }
    foreach((array)($report['research_notes']??[])as$note){if(!empty($note['text']))$card((string)($note['provider']??'AI research'),(string)$note['text'],'#F8FAFC','#9AA8BF');}

    $section('Search results - '.count((array)($report['results']??[])));
    $results=(array)($report['results']??[]);
    if(!$results)$paragraph('No indexed results were returned for this search.',10,'#64748B',86,'F3');
    foreach($results as$i=>$r){
        $title=(string)($r['title']??'Untitled result');$titleLines=aih_pdf_wrap(($i+1).'. '.$title,72);
        $details=[];
        $sector=(string)($r['sector']??'ALL');if($sector!=='ALL')$details[]='Sector: '.aih_pdf_sector_label($sector);
        foreach(['country'=>'Country','brand'=>'Brand','make'=>'Make','model'=>'Model','year'=>'Year','mileage'=>'Mileage','area'=>'Area','location'=>'Location','company'=>'Company','salary'=>'Salary','condition'=>'Condition']as$k=>$label)if(!empty($r[$k]))$details[]=$label.': '.$r[$k];
        if(!empty($r['price']))$details[]='Price: '.$r['price'].' '.($r['currency']??'');
        $detailLines=aih_pdf_wrap(implode('  |  ',array_values(array_unique($details))),88);
        $snippetLines=array_slice(aih_pdf_wrap((string)($r['snippet']??''),92),0,5);
        $url=aih_pdf_trim_url((string)($r['url']??''));$urlLines=aih_pdf_wrap('Source: '.$url,94);
        $h=18+count($titleLines)*12+($details?count($detailLines)*10+4:0)+($snippetLines?count($snippetLines)*10+6:0)+count($urlLines)*9+12;
        $need($h+8);
        $stream.=aih_pdf_rect(36,$y-$h+6,523,$h,'#FFFFFF','#E2E8F0',0.65);
        $stream.=aih_pdf_rect(36,$y-$h+6,5,$h,($i%2===0?'#4F6FFF':'#21A1A1'));
        $yy=$y-12;foreach($titleLines as$l){$stream.=aih_pdf_text(50,$yy,$l,9.5,'F2','#15223A');$yy-=12;}
        if($details){foreach($detailLines as$l){$stream.=aih_pdf_text(50,$yy,$l,7.8,'F1','#40516A');$yy-=10;}$yy-=2;}
        foreach($snippetLines as$l){$stream.=aih_pdf_text(50,$yy,$l,8,'F1','#5B6678');$yy-=10;}$yy-=2;
        foreach($urlLines as$l){$stream.=aih_pdf_text(50,$yy,$l,7.4,'F1','#2457A7');$yy-=9;}
        $y-=$h+10;
    }
    if($stream!=='')$pages[]=$stream;

    $objects=[];$objects[1]='<< /Type /Catalog /Pages 2 0 R >>';
    $objects[3]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
    $objects[4]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
    $objects[5]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique >>';
    $pageObjIds=[];$contentIds=[];$next=6;
    foreach($pages as$_){$pageObjIds[]=$next++;$contentIds[]=$next++;}
    $kids=implode(' ',array_map(fn($id)=>$id.' 0 R',$pageObjIds));$objects[2]='<< /Type /Pages /Kids [ '.$kids.' ] /Count '.count($pages).' >>';
    $total=count($pages);
    foreach($pages as$pi=>$body){
        $footer=aih_pdf_rect(36,28,523,1,'#E6EBF3').aih_pdf_text(36,14,'Free AI Hub 63 - Smart Search',7.2,'F1','#7A879C').aih_pdf_text(500,14,'Page '.($pi+1).' / '.$total,7.2,'F1','#7A879C');
        $content=$body.$footer;$cid=$contentIds[$pi];$pid=$pageObjIds[$pi];
        $objects[$cid]='<< /Length '.strlen($content).' >>' . "\nstream\n".$content."endstream";
        $objects[$pid]='<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R >> >> /Contents '.$cid.' 0 R >>';
    }
    ksort($objects);$pdf="%PDF-1.4\n";$offsets=[0=>0];foreach($objects as$id=>$obj){$offsets[$id]=strlen($pdf);$pdf.=$id." 0 obj\n".$obj."\nendobj\n";}
    $xref=strlen($pdf);$max=max(array_keys($objects));$pdf.="xref\n0 ".($max+1)."\n0000000000 65535 f \n";for($i=1;$i<=$max;$i++)$pdf.=sprintf('%010d 00000 n ',$offsets[$i]??0)."\n";
    $pdf.="trailer\n<< /Size ".($max+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";return $pdf;
}
