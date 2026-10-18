<?php
/** Fixed owner notification, only called for a confirmed website booking. */
function sonic_booking_notify_owner(array $event,string $receipt): void {
    if(!preg_match('/^[a-f0-9]{64}$/',$receipt)||empty($event['id'])||($event['status']??'confirmed')==='cancelled') return;
    $path=calendar_runtime().'/owner-notice-'.$receipt.'.json';
    // Claim before SMTP: an ambiguous transport result must never cause duplicate mail.
    $claim=@fopen($path,'x');
    if(!$claim) return;
    @chmod($path,0600);
    fwrite($claim,json_encode(['state'=>'sending','at'=>time()]));fclose($claim);
    $state='failed';
    try {
        if(!function_exists('wp_mail')) throw new RuntimeException('Mail transport unavailable');
        $tz=new DateTimeZone('America/Toronto');
        $start=(new DateTimeImmutable($event['start']['dateTime']))->setTimezone($tz);
        $end=(new DateTimeImmutable($event['end']['dateTime']))->setTimezone($tz);
        $customer=$event['attendees'][0]??[];
        $name=preg_replace('/[\r\n]+/',' ',(string)($customer['displayName']??''));
        $email=(string)($customer['email']??'');
        $link=(string)($event['htmlLink']??'');
        if(!str_starts_with($link,'https://www.google.com/calendar/')&&!str_starts_with($link,'https://calendar.google.com/')) $link='https://calendar.google.com/';
        $body="Une réservation a été confirmée depuis le site Sonic Pro.\n\n".
            "Client : ".$name."\nCourriel : ".$email."\n".
            "Date : ".$start->format('Y-m-d')."\nHoraire : ".$start->format('H:i')." à ".$end->format('H:i')." (America/Toronto)\n\n".
            (string)($event['description']??'')."\n\nOuvrir le rendez-vous : ".$link."\n\n".
            "Il s’agit d’un appel initial de 20 minutes. Aucune intervention sur place n’est confirmée par ce courriel.\n";
        $sent=wp_mail(['sonic.pro.electricite@gmail.com','support@sonicproelectricite.ca'],'Sonic Pro — Nouveau rendez-vous du site — '.$start->format('Y-m-d H:i'),$body,['Content-Type: text/plain; charset=UTF-8']);
        $state=$sent?'sent':'failed';
    } catch(Throwable $e) { $state='failed'; }
    file_put_contents($path,json_encode(['state'=>$state,'at'=>time(),'event_id'=>$event['id']]),LOCK_EX);
    if($state!=='sent') error_log('Sonic booking owner notification failed; inspect receipt '.$receipt.' before retrying.');
}
