<?php
/** Move a single verified RFC Message-ID to Traite and mark it read. */
function sonic_imap_command($stream,string $tag,string $command): string {
    fwrite($stream,$tag.' '.$command."\r\n");$response='';
    while(($line=fgets($stream,8192))!==false){$response.=$line;if(strlen($response)>131072)throw new RuntimeException('IMAP response too large');if(str_starts_with($line,$tag.' ')){if(!preg_match('/^'.preg_quote($tag,'/').' OK\b/i',$line))throw new RuntimeException('IMAP command rejected: '.$tag.' '.strtok($command,' ').(strtok($command,' ')==='UID'?' '.substr(trim($line),0,240):''));return $response;}}
    throw new RuntimeException('IMAP disconnected');
}
function sonic_imap_quote(string $text): string {
    if(preg_match('/[\r\n\x00]/',$text))throw new RuntimeException('Invalid IMAP value');
    return '"'.str_replace(['\\','"'],['\\\\','\\"'],$text).'"';
}
function sonic_mark_mail_processed(string $messageId): array {
    if(strlen($messageId)>998||!preg_match('/^<[^<>\s]+>$/',$messageId))throw new RuntimeException('Invalid Message-ID');
    $config=calendar_json(calendar_private().'/support-mail.json');
    if(($config['username']??'')!=='support@sonicproelectricite.ca'||empty($config['password']))throw new RuntimeException('Mailbox unavailable');
    $ctx=stream_context_create(['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'peer_name'=>'imap.hostinger.com']]);
    $stream=stream_socket_client('ssl://imap.hostinger.com:993',$errno,$error,15,STREAM_CLIENT_CONNECT,$ctx);
    if(!$stream)throw new RuntimeException('IMAP unavailable');stream_set_timeout($stream,20);
    try {
        if(!preg_match('/^\* OK\b/i',fgets($stream,8192)))throw new RuntimeException('IMAP greeting rejected');
        sonic_imap_command($stream,'a1','LOGIN '.sonic_imap_quote($config['username']).' '.sonic_imap_quote($config['password']));unset($config);
        $sequence=2;$capabilities=sonic_imap_command($stream,'a'.$sequence++,'CAPABILITY');$found=null;
        foreach(['INBOX','INBOX.Traite'] as $folder){
            try{sonic_imap_command($stream,'a'.$sequence++,'SELECT '.sonic_imap_quote($folder));}catch(RuntimeException $e){if($folder==='INBOX.Traite')break;throw $e;}
            $search=sonic_imap_command($stream,'a'.$sequence++,'UID SEARCH HEADER Message-ID '.sonic_imap_quote($messageId));
            if(!preg_match('/^\* SEARCH(?: ([0-9 ]+))?\r?$/mi',$search,$match))throw new RuntimeException('Invalid IMAP search');
            $uids=preg_split('/\s+/',trim($match[1]??''),-1,PREG_SPLIT_NO_EMPTY);
            if(count($uids)>1)throw new RuntimeException('Ambiguous Message-ID');
            if(!$uids)continue;$uid=$uids[0];
            $header=sonic_imap_command($stream,'a'.$sequence++,'UID FETCH '.$uid.' (BODY.PEEK[HEADER.FIELDS (MESSAGE-ID)])');
            if(!preg_match('/^Message-ID:[ \t]*([^\r\n]+)/mi',$header,$m)||trim($m[1])!==$messageId)throw new RuntimeException('Message-ID mismatch');
            sonic_imap_command($stream,'a'.$sequence++,'UID STORE '.$uid.' +FLAGS.SILENT (\\Seen)');
            if($folder==='INBOX'){
                // CREATE can report already exists; SELECT/MOVE remains authoritative.
                try{sonic_imap_command($stream,'a'.$sequence++,'CREATE "INBOX.Traite"');}catch(RuntimeException $e){}
                if(preg_match('/\bMOVE\b/i',$capabilities)) {
                    sonic_imap_command($stream,'a'.$sequence++,'UID MOVE '.$uid.' "INBOX.Traite"');
                } elseif(preg_match('/\bUIDPLUS\b/i',$capabilities)) {
                    // UID EXPUNGE affects only this exact message, never other deleted mail.
                    sonic_imap_command($stream,'a'.$sequence++,'UID COPY '.$uid.' "INBOX.Traite"');
                    sonic_imap_command($stream,'a'.$sequence++,'UID STORE '.$uid.' +FLAGS.SILENT (\\Deleted)');
                    sonic_imap_command($stream,'a'.$sequence++,'UID EXPUNGE '.$uid);
                } else {throw new RuntimeException('IMAP safe move unavailable');}
            }
            $found=['processed'=>true,'folder'=>'Traite','read'=>true];break;
        }
        if(!$found)throw new RuntimeException('Message not found');
        return $found;
    }finally{fclose($stream);}
}
