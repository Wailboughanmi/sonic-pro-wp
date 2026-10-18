<?php
require __DIR__.'/schedule.php';
require_once __DIR__.'/owner-notification.php';
require_once __DIR__.'/automation-tracking.php';
if(!function_exists('calendar_http')) require __DIR__.'/google.php';
ini_set('display_errors','0');
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');header('X-Robots-Tag: noindex, nofollow');
function reply(array $data,int $status=200): never {http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;}
function calendar_runtime(): string { return getenv('CALENDAR_RUNTIME')?:'/var/calendar-runtime'; }
// Only trust X-Real-IP behind the explicitly configured local reverse proxy.
function calendar_throttle(string $kind,int $limit,int $seconds): void {
    $ip=getenv('CALENDAR_TRUST_PROXY')==='1' ? ($_SERVER['HTTP_X_REAL_IP']??$_SERVER['REMOTE_ADDR']??'unknown') : ($_SERVER['REMOTE_ADDR']??'unknown');
    $path=calendar_runtime().'/rate-'.hash('sha256',$kind.'|'.$ip).'.json';
    $f=fopen($path,'c+');flock($f,LOCK_EX);
    $times=json_decode(stream_get_contents($f),true)?:[];
    $times=array_values(array_filter($times,fn($t)=>is_int($t)&&$t>time()-$seconds));
    if(count($times)>=$limit){flock($f,LOCK_UN);fclose($f);reply(['error'=>'Trop de demandes. Veuillez réessayer dans quelques minutes.'],429);}
    $times[]=time();rewind($f);ftruncate($f,0);fwrite($f,json_encode($times));flock($f,LOCK_UN);fclose($f);
}
$config=calendar_json(__DIR__.'/config.json');
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$method=$_SERVER['REQUEST_METHOD'];
sonic_automation_route($path,$method,$config);
$origin=getenv('CALENDAR_ORIGIN')?:'http://localhost:8097';
if(!empty($_SERVER['HTTP_ORIGIN'])&&$_SERVER['HTTP_ORIGIN']!==$origin) reply(['error'=>'Origine non autorisée.'],403);
if(isset($_SERVER['HTTP_SEC_FETCH_SITE'])&&$_SERVER['HTTP_SEC_FETCH_SITE']==='cross-site') reply(['error'=>'Requête non autorisée.'],403);
session_name('sonic_booking');session_set_cookie_params(['httponly'=>true,'samesite'=>'Strict','secure'=>str_starts_with($origin,'https://'),'path'=>'/calendar-api/']);session_start();
$_SESSION['csrf']??=bin2hex(random_bytes(32));
if($method==='GET'&&$path==='/calendar-api/availability') {
    calendar_throttle('availability',60,60);
    $month=$_GET['month']??'';
    $tz=new DateTimeZone($config['timezone']);$now=new DateTimeImmutable('now',$tz);
    if(!is_string($month)||!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$month)||$month<$now->format('Y-m')||$month>$now->modify('+'.$config['horizon_days'].' days')->format('Y-m')) reply(['error'=>'Mois hors de la période de réservation.'],400);
    if(!is_file(calendar_private().'/google-calendar-tokens.json')) reply(['error'=>'La connexion au calendrier est en cours de finalisation. Vous pouvez réserver avec le lien Google ci-dessous.','connected'=>false],503);
    try {$busy=calendar_busy($config,$month);$slots=sonic_slots($config,$month,$busy,$now);}
    catch(Throwable $e){reply(['error'=>'Les disponibilités Google sont temporairement indisponibles. Réessayez ou utilisez le lien Google.'],503);}
    reply(['month'=>$month,'timezone'=>$config['timezone'],'duration'=>$config['duration_minutes'],'slots'=>(object)$slots,'csrf'=>$_SESSION['csrf'],'bookingEnabled'=>$config['booking_enabled']]);
}
if($method==='POST'&&$path==='/calendar-api/book') {
    if(($_SERVER['HTTP_ORIGIN']??'')!==$origin) reply(['error'=>'Origine non autorisée.'],403);
    if(!hash_equals($_SESSION['csrf'],$_SERVER['HTTP_X_BOOKING_CSRF']??'')) reply(['error'=>'Rechargez le calendrier pour poursuivre.'],403);
    if(!$config['booking_enabled']) reply(['error'=>'La réservation directe est en cours de configuration. Utilisez le calendrier Google.'],503);
    if((int)($_SERVER['CONTENT_LENGTH']??0)>8192) reply(['error'=>'Formulaire trop volumineux.'],413);
    $data=json_decode(file_get_contents('php://input'),true);
    if(!is_array($data)) reply(['error'=>'Formulaire invalide.'],400);
    foreach(['name','email','phone','start','website','requestId'] as $field){if(!isset($data[$field])||!is_string($data[$field]))reply(['error'=>'Formulaire invalide.'],400);}
    $name=trim($data['name']);$email=trim($data['email']);$phone=trim($data['phone']);
    if(!$name||mb_strlen($name)>100||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>254||!preg_match('/^[+()0-9 .-]{7,30}$/',$phone)||$data['website']!==''||($data['consent']??false)!==true||!preg_match('/^[a-f0-9-]{36}$/',$data['requestId'])) reply(['error'=>'Vérifiez votre nom, courriel, téléphone et consentement.'],400);
    calendar_throttle('booking',5,900);
    $attempts=array_filter($_SESSION['attempts']??[],fn($t)=>$t>time()-900);
    if(count($attempts)>=5) reply(['error'=>'Trop de demandes. Veuillez nous appeler.'],429);
    $attempts[]=time();$_SESSION['attempts']=$attempts;
    $receipt=hash('sha256',$data['requestId'].'|'.strtolower($email).'|'.$data['start']);
    if(isset($_SESSION['receipts'][$receipt])) reply($_SESSION['receipts'][$receipt]);
    try {
        // Bootstrap WordPress in global scope for the existing SMTP transport.
        $mailBootstrap=dirname(__DIR__,2).'/public_html/wp-load.php';
        if(!function_exists('wp_mail')&&is_file($mailBootstrap)) require_once $mailBootstrap;
        $tz=new DateTimeZone($config['timezone']);$start=new DateTimeImmutable($data['start']);$start=$start->setTimezone($tz);$month=$start->format('Y-m');
        $now=new DateTimeImmutable('now',$tz);
        if($start<$now->modify('+'.$config['notice_minutes'].' minutes')||$start>$now->modify('+'.$config['horizon_days'].' days')) reply(['error'=>'Créneau hors de la période de réservation.'],400);
        $lock=fopen(calendar_runtime().'/book.lock','c');flock($lock,LOCK_EX);
        $id=hash('sha256','sonic-'.$receipt);
        $eventUrl='https://www.googleapis.com/calendar/v3/calendars/'.rawurlencode($config['calendar_id']).'/events';
        if(isset($_SESSION['pending'][$receipt])) {
            try {
                $existing=calendar_http($eventUrl.'/'.$id,null,calendar_token());
                if(($existing['extendedProperties']['private']['sonicBooking']??'')===$receipt && ($existing['status']??'')!=='cancelled') {
                    sonic_booking_notify_owner($existing,$receipt);
                    $confirmation=['confirmed'=>true,'start'=>$existing['start']['dateTime'],'end'=>$existing['end']['dateTime'],'timezone'=>$config['timezone']];
                    $_SESSION['receipts'][$receipt]=$confirmation;unset($_SESSION['pending'][$receipt]);reply($confirmation);
                }
                reply(['error'=>'Cette réservation a été modifiée. Veuillez nous appeler.'],409);
            } catch(CalendarUnavailable $e) { if($e->getCode()!==404) throw $e; }
        }
        $slots=sonic_slots($config,$month,calendar_busy($config,$month));$chosen=null;
        foreach($slots[$start->format('Y-m-d')]??[] as $slot){if($slot['start']===$data['start'])$chosen=$slot;}
        if(!$chosen) reply(['error'=>'Ce créneau n’est plus disponible. Choisissez un autre horaire.'],409);
        $id=hash('sha256','sonic-'.$receipt);
        $event=['id'=>$id,'summary'=>'Appel initial Sonic-Pro — '.$name,'description'=>'Réservation depuis le site Sonic-Pro. Téléphone : '.$phone,'start'=>['dateTime'=>$chosen['start'],'timeZone'=>$config['timezone']],'end'=>['dateTime'=>$chosen['end'],'timeZone'=>$config['timezone']],'attendees'=>[['email'=>$email,'displayName'=>$name]],'visibility'=>'private','transparency'=>'opaque','guestsCanModify'=>false,'guestsCanInviteOthers'=>false,'extendedProperties'=>['private'=>['sonicBooking'=>$receipt]]];
        $_SESSION['pending'][$receipt]=true;
        $result=calendar_http($eventUrl.'?sendUpdates=all',$event,calendar_token());
        if(($result['id']??'')!==$id) throw new CalendarUnavailable('Booking unconfirmed');
        sonic_booking_notify_owner($result,$receipt);
        $confirmation=['confirmed'=>true,'start'=>$chosen['start'],'end'=>$chosen['end'],'timezone'=>$config['timezone']];$_SESSION['receipts'][$receipt]=$confirmation;unset($_SESSION['pending'][$receipt]);
        reply($confirmation);
    }catch(Throwable $e){reply(['error'=>'La réservation n’a pas pu être confirmée. Vérifiez votre courriel avant de réessayer, ou appelez-nous.'],503);}
}
reply(['error'=>'Page introuvable.'],404);
