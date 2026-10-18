<?php
class CalendarUnavailable extends RuntimeException {}
function calendar_private(): string { return getenv('CALENDAR_SECRETS')?:'/var/calendar-private'; }
function calendar_json(string $path): array {
    if(!is_file($path)) throw new CalendarUnavailable('Calendar authorization missing');
    return json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
}
function calendar_http(string $url, ?array $body=null, ?string $token=null, bool $form=false): array {
    $curl=curl_init($url);
    $headers=['Accept: application/json'];
    if($token) $headers[]='Authorization: Bearer '.$token;
    if($body!==null) $headers[]='Content-Type: '.($form?'application/x-www-form-urlencoded':'application/json');
    curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20,CURLOPT_CONNECTTIMEOUT=>7,CURLOPT_HTTPHEADER=>$headers,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS]);
    if($body!==null) curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$form?http_build_query($body):json_encode($body)]);
    $raw=curl_exec($curl);$code=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);curl_close($curl);
    if($raw===false||$code<200||$code>=300) throw new CalendarUnavailable('Google Calendar request failed',$code);
    return json_decode($raw,true,512,JSON_THROW_ON_ERROR);
}
function calendar_token(): string {
    $dir=calendar_private();$lock=fopen($dir.'/token.lock','c');flock($lock,LOCK_EX);
    try {
        $path=$dir.'/google-calendar-tokens.json';$token=calendar_json($path);
        if(($token['expires_at']??0)>time()+120) return $token['access_token'];
        $client=calendar_json($dir.'/google-calendar-oauth.json')['web'];
        $fresh=calendar_http('https://oauth2.googleapis.com/token',['client_id'=>$client['client_id'],'client_secret'=>$client['client_secret'],'refresh_token'=>$token['refresh_token'],'grant_type'=>'refresh_token'],null,true);
        $token=array_merge($token,$fresh,['expires_at'=>time()+$fresh['expires_in']]);
        $temp=$path.'.tmp';file_put_contents($temp,json_encode($token),LOCK_EX);chmod($temp,0600);rename($temp,$path);
        return $token['access_token'];
    } finally { flock($lock,LOCK_UN);fclose($lock); }
}
function calendar_busy(array $config,string $month): array {
    $tz=new DateTimeZone($config['timezone']);$start=new DateTimeImmutable($month.'-01',$tz);
    $result=calendar_http('https://www.googleapis.com/calendar/v3/freeBusy',['timeMin'=>$start->format(DATE_RFC3339),'timeMax'=>$start->modify('first day of next month')->format(DATE_RFC3339),'timeZone'=>$config['timezone'],'items'=>[['id'=>$config['calendar_id']]]],calendar_token());
    $calendar=$result['calendars'][$config['calendar_id']]??null;
    if(!$calendar||!empty($calendar['errors'])||!isset($calendar['busy'])) throw new CalendarUnavailable('Availability unavailable');
    return $calendar['busy'];
}
