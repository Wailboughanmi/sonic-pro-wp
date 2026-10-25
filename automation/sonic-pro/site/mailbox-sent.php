<?php
/** Archive SMTP-accepted acknowledgements only. This code never sends email. */
require_once __DIR__.'/mailbox-processed.php';
function sonic_sent_mime(array $data): array {
    $id=$data['smtp_message_id']??null;$to=$data['recipient']??null;
    $subject=$data['subject']??null;$html=$data['html_body']??null;$sent=$data['sent_at']??null;
    if(!is_string($id)||strlen($id)>200||!preg_match('/^<[a-zA-Z0-9._-]+@sonicproelectricite\.ca>$/D',$id))throw new InvalidArgumentException('Invalid outgoing Message-ID');
    if(!is_string($to)||strlen($to)>254||preg_match('/[\r\n\x00<>]/',$to)||!filter_var($to,FILTER_VALIDATE_EMAIL))throw new InvalidArgumentException('Invalid recipient');
    if(!is_string($subject)||strlen($subject)>998||preg_match('/[\r\n\x00]/',$subject))throw new InvalidArgumentException('Invalid subject');
    if(!is_string($html)||strlen($html)>196608||!str_contains($html,'sonicproelectricite.ca'))throw new InvalidArgumentException('Invalid email body');
    if(!is_string($sent)||!preg_match('/^\d{4}-\d{2}-\d{2}[T ][0-9:.+-]+(?:Z)?$/D',$sent)||($time=strtotime($sent))===false||$time>time()+300||$time<strtotime('2026-01-01'))throw new InvalidArgumentException('Invalid sent date');
    $chars=preg_split('//u',$subject,-1,PREG_SPLIT_NO_EMPTY);if($chars===false)throw new InvalidArgumentException('Invalid subject');
    $parts=[];$part='';foreach($chars as $char){if(strlen($part.$char)>42){$parts[]=$part;$part='';}$part.=$char;}$parts[]=$part;
    $encodedSubject=implode("\r\n ",array_map(fn($part)=>'=?UTF-8?B?'.base64_encode($part).'?=',$parts));
    $mime='Date: '.gmdate('D, d M Y H:i:s O',$time)."\r\n".
        'From: Sonic Pro <support@sonicproelectricite.ca>'."\r\n".'To: '.$to."\r\n".
        'Reply-To: support@sonicproelectricite.ca'."\r\n".'Message-ID: '.$id."\r\n".
        'Subject: '.$encodedSubject."\r\n".'MIME-Version: 1.0'."\r\n".
        'Content-Type: text/html; charset=UTF-8'."\r\n".'Content-Transfer-Encoding: base64'."\r\n\r\n".
        chunk_split(base64_encode($html),76,"\r\n");
    return [$id,$mime,$time];
}
function sonic_sent_uid($stream,string $folder,string $id,int &$sequence): ?string {
    sonic_imap_command($stream,'s'.$sequence++,'SELECT '.sonic_imap_quote($folder));
    $response=sonic_imap_command($stream,'s'.$sequence++,'UID SEARCH HEADER Message-ID '.sonic_imap_quote($id));
    if(!preg_match('/^\* SEARCH(?: ([0-9 ]+))?\r?$/mi',$response,$m))throw new RuntimeException('Invalid IMAP search');
    $uids=preg_split('/\s+/',trim($m[1]??''),-1,PREG_SPLIT_NO_EMPTY);
    if(count($uids)>1)throw new RuntimeException('Duplicate outgoing Message-ID');
    if(!$uids)return null;
    $header=sonic_imap_command($stream,'s'.$sequence++,'UID FETCH '.$uids[0].' (BODY.PEEK[HEADER.FIELDS (MESSAGE-ID)])');
    if(!preg_match('/^Message-ID:[ \t]*([^\r\n]+)/mi',$header,$h)||trim($h[1])!==$id)throw new RuntimeException('Message-ID mismatch');
    return $uids[0];
}
function sonic_imap_write_all($stream,string $bytes): void {
    $offset=0;while($offset<strlen($bytes)){$n=fwrite($stream,substr($bytes,$offset));if(!$n)throw new RuntimeException('IMAP write failed');$offset+=$n;}
}
function sonic_archive_sent_copy(array $data): array {
    [$id,$mime,$time]=sonic_sent_mime($data);
    // Serialize retries for this same SMTP Message-ID, including ambiguous APPEND timeouts.
    $lock=fopen(calendar_runtime().'/sent-copy-'.hash('sha256',$id).'.lock','c');
    if(!$lock||!flock($lock,LOCK_EX|LOCK_NB))throw new RuntimeException('Archive already in progress');
    $stream=null;
    try {
        $config=calendar_json(calendar_private().'/support-mail.json');
        if(($config['username']??'')!=='support@sonicproelectricite.ca'||empty($config['password']))throw new RuntimeException('Mailbox unavailable');
        $ctx=stream_context_create(['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'peer_name'=>'imap.hostinger.com']]);
        $stream=stream_socket_client('ssl://imap.hostinger.com:993',$errno,$error,15,STREAM_CLIENT_CONNECT,$ctx);
        if(!$stream)throw new RuntimeException('IMAP unavailable');stream_set_timeout($stream,20);
        if(!preg_match('/^\* OK\b/i',fgets($stream,8192)))throw new RuntimeException('IMAP greeting rejected');
        $sequence=1;sonic_imap_command($stream,'s'.$sequence++,'LOGIN '.sonic_imap_quote($config['username']).' '.sonic_imap_quote($config['password']));unset($config);
        $list=sonic_imap_command($stream,'s'.$sequence++,'LIST "" "*"');$folder=null;$fallback=null;
        foreach(explode("\n",$list) as $line){
            if(preg_match('/^\* LIST \(([^)]*)\) (?:"[^"]*"|NIL) ("(?:[^"\\\\]|\\\\.)*"|[^\r\n]+)\r?$/i',trim($line),$m)){
                $name=$m[2];if(str_starts_with($name,'"'))$name=stripcslashes(substr($name,1,-1));
                if(preg_match('/(?:^|\s)\\\\Sent(?:\s|$)/i',$m[1]))$folder=$name;
                if(in_array($name,['INBOX.Sent','Sent'],true))$fallback=$name;
            }
        }
        $folder=$folder??$fallback;
        // Fail closed rather than create a folder the webmail may not display as Sent.
        if(!$folder)throw new RuntimeException('Sent folder not found');
        $uid=sonic_sent_uid($stream,$folder,$id,$sequence);$existed=$uid!==null;
        if(!$uid){
            $tag='s'.$sequence++;
            sonic_imap_write_all($stream,$tag.' APPEND '.sonic_imap_quote($folder).' (\\Seen) "'.gmdate('d-M-Y H:i:s O',$time).'" {'.strlen($mime)."}\r\n");
            $line=fgets($stream,8192);if($line===false||!str_starts_with($line,'+'))throw new RuntimeException('IMAP APPEND continuation rejected');
            sonic_imap_write_all($stream,$mime."\r\n");$ok=false;
            while(($line=fgets($stream,8192))!==false){if(str_starts_with($line,$tag.' ')){$ok=(bool)preg_match('/^'.preg_quote($tag,'/').' OK\b/i',$line);break;}}
            if(!$ok)throw new RuntimeException('IMAP APPEND result uncertain');
            $uid=sonic_sent_uid($stream,$folder,$id,$sequence);
            if(!$uid)throw new RuntimeException('IMAP APPEND verification failed');
        }
        return ['archived'=>true,'already_present'=>$existed,'smtp_message_id'=>$id,'folder'=>$folder,'uid'=>$uid];
    }finally{if(is_resource($stream))fclose($stream);flock($lock,LOCK_UN);fclose($lock);}
}
