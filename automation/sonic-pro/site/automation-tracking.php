<?php
/** Public opaque click redirect and authenticated, read-only automation export. */
function sonic_automation_route(string $path,string $method,array $config): void {
    if($path==='/calendar-api/email-call'&&in_array($method,['GET','HEAD'],true)) {
        $token=$_GET['t']??'';
        if(!is_string($token)||!preg_match('/^[a-f0-9]{64}$/',$token))reply(['error'=>'Lien invalide.'],400);
        if($method==='GET') {
            calendar_throttle('email-calendar-link',30,60);
            $record=['click_id'=>bin2hex(random_bytes(16)),'token'=>$token,'clicked_at'=>gmdate(DATE_RFC3339)];
            $file=calendar_runtime().'/email-clicks-'.gmdate('Y-m-d').'.jsonl';
            $f=@fopen($file,'a');
            if($f){flock($f,LOCK_EX);if(fstat($f)['size']<4194304)fwrite($f,json_encode($record)."\n");flock($f,LOCK_UN);fclose($f);@chmod($file,0600);}
        }
        header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');
        header('Location: https://calendar.app.google/MGyzFcUC1rWqWHMp6',true,302);exit;
    }
    if(!in_array($path,['/calendar-api/automation-events','/calendar-api/mailbox-processed'],true))return;
    $keyPath=calendar_private().'/automation-reporting-key';
    $expected=is_file($keyPath)?trim(file_get_contents($keyPath)):'';
    $provided=$_SERVER['HTTP_X_SONIC_AUTOMATION_KEY']??'';
    if(strlen($expected)<32||!is_string($provided)||!hash_equals($expected,$provided))reply(['error'=>'Unauthorized'],401);
    if($path==='/calendar-api/mailbox-processed') {
        if($method!=='POST')reply(['error'=>'Method not allowed'],405);
        if((int)($_SERVER['CONTENT_LENGTH']??0)>2048)reply(['error'=>'Payload too large'],413);
        $data=json_decode(file_get_contents('php://input'),true);
        require_once __DIR__.'/mailbox-processed.php';
        try {reply(sonic_mark_mail_processed((string)($data['message_id']??'')));}
        catch(Throwable $e){$safe=['Calendar authorization missing','Invalid Message-ID','Mailbox unavailable','IMAP unavailable','IMAP greeting rejected','IMAP command rejected','IMAP response too large','IMAP disconnected','Invalid IMAP search','Ambiguous Message-ID','Message-ID mismatch','Message not found'];reply(['error'=>'Mailbox processing failed; inspect request before retrying','stage'=>(in_array($e->getMessage(),$safe,true)||preg_match('/^IMAP command rejected: a[0-9]+ (LOGIN|SELECT|UID|CREATE)(?: [^\r\n]{0,240})?$/',$e->getMessage()))?$e->getMessage():'Mailbox configuration or transport error'],503);}
    }
    if($method!=='GET')reply(['error'=>'Method not allowed'],405);
    $clicks=[];$cutoff=time()-8*86400;
    foreach(glob(calendar_runtime().'/email-clicks-????-??-??.jsonl')?:[] as $file) {
        $day=substr(basename($file),13,10);$time=strtotime($day.'T00:00:00Z');
        if($time===false||$time<$cutoff-86400)continue;
        $f=fopen($file,'r');while(($line=fgets($f))!==false){$row=json_decode($line,true);if(is_array($row)&&strtotime($row['clicked_at']??'')>=$cutoff)$clicks[]=$row;if(count($clicks)>40000){fclose($f);reply(['error'=>'Export limit reached; inspect backlog'],503);}}fclose($f);
    }
    try {
        $events=[];$next=null;$page=0;
        do {
            $query=['updatedMin'=>gmdate(DATE_RFC3339,$cutoff),'showDeleted'=>'true','maxResults'=>2500,'fields'=>'nextPageToken,items(id,status,created,updated,start,end,attendees(email),extendedProperties/private)'];
            if($next)$query['pageToken']=$next;
            $result=calendar_http('https://www.googleapis.com/calendar/v3/calendars/'.rawurlencode($config['calendar_id']).'/events?'.http_build_query($query),null,calendar_token());
            foreach($result['items']??[] as $event) {
                $emails=[];
                foreach($event['attendees']??[] as $attendee){$email=strtolower(trim($attendee['email']??''));if(filter_var($email,FILTER_VALIDATE_EMAIL)&&!in_array($email,['sonic.pro.electricite@gmail.com','support@sonicproelectricite.ca'],true))$emails[]=$email;}
                if(!$emails&&($event['status']??'')!=='cancelled')continue;
                $events[]=['event_id'=>$event['id'],'status'=>$event['status']??'confirmed','event_created_at'=>$event['created']??null,'event_updated_at'=>$event['updated']??null,'starts_at'=>$event['start']['dateTime']??null,'ends_at'=>$event['end']['dateTime']??null,'attendee_emails'=>array_values(array_unique($emails)),'source'=>isset($event['extendedProperties']['private']['sonicBooking'])?'native_website':'google_calendar'];
            }
            $next=$result['nextPageToken']??null;
            if(++$page>=10&&$next)throw new RuntimeException('Calendar export limit');
        } while($next);
        reply(['exported_at'=>gmdate(DATE_RFC3339),'clicks'=>$clicks,'bookings'=>$events,'lookback_days'=>8]);
    }catch(Throwable $e){reply(['error'=>'Calendar export unavailable'],503);}
}
