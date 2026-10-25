"""Deploy the authenticated Sent archive helper after server-side PHP checks."""
from pathlib import Path
import ftplib,ssl,json,io,time,os,urllib.request,urllib.error
os.umask(0o077)
stage=Path(__file__).resolve().parent
root=Path('/Users/kingkong/Documents/workspace/sonic-pro-prod/rebuild')
private='/domains/sonicproelectricite.ca/sonic-private'
public='/domains/sonicproelectricite.ca/public_html'
credentials=json.loads((root/'secrets/hostinger-ftp.json').read_text())
f=ftplib.FTP_TLS(context=ssl.create_default_context(),timeout=60);f.connect('82.25.87.142',21);f.host='hstgr.io';f.auth();f.login('u714692902',credentials['password']);f.prot_p();del credentials

def get(path):
 b=io.BytesIO();f.retrbinary('RETR '+path,b.write);return b.getvalue()
def put(path,data):
 f.storbinary('STOR '+path,io.BytesIO(data))
 if get(path)!=data:raise RuntimeError('Readback verification failed')
old=get(private+'/calendar/automation-tracking.php')
if old!=(root/'calendar/automation-tracking.php').read_bytes():raise SystemExit('Live tracking route differs; stopped')
old_rules=get(public+'/calendar-api/.htaccess')
if old_rules!=(root/'deploy/calendar.htaccess').read_bytes():raise SystemExit('Live rewrite rules differ; stopped')
key=get(private+'/credentials/automation-reporting-key').decode().strip()
ident='sent-archive-'+str(time.time_ns());backup=Path('/private/tmp')/ident;backup.mkdir(mode=0o700)
(backup/'automation-tracking.php').write_bytes(old)
(backup/'calendar.htaccess').write_bytes(old_rules)
probe=public+'/'+ident+'.php'
files=['mailbox-sent.php','automation-tracking.php']
staged=[]
try:
 for name in files:
  remote=private+'/calendar/'+ident+'-'+name;put(remote,(stage/name).read_bytes());staged.append(remote)
 php='''<?php
 ini_set('display_errors','0');header('Content-Type: application/json');header('Cache-Control: no-store');
 $private=dirname(__DIR__).'/sonic-private';$key=trim(file_get_contents($private.'/credentials/automation-reporting-key'));
 if(!hash_equals($key,$_SERVER['HTTP_X_SONIC_AUTOMATION_KEY']??'')){http_response_code(401);exit;}
 try {
 foreach(['mailbox-sent.php','automation-tracking.php'] as $name)token_get_all(file_get_contents($private.'/calendar/IDENT-'.$name),TOKEN_PARSE);
 require $private.'/calendar/IDENT-mailbox-sent.php';
 $sample=['smtp_message_id'=>'<unit-test@sonicproelectricite.ca>','recipient'=>'test@example.com','subject'=>'Demande reçue — Électricité','html_body'=>'<p>sonicproelectricite.ca</p>','sent_at'=>gmdate(DATE_RFC3339)];
 [$id,$mime,$time]=sonic_sent_mime($sample);
 if(!str_contains($mime,'Message-ID: '.$id)||!str_contains($mime,base64_encode($sample['html_body'])))throw new RuntimeException('MIME test failed');
 $sample['recipient']="test@example.com\\r\\nBcc: other@example.com";
 try{sonic_sent_mime($sample);throw new RuntimeException('Header injection accepted');}catch(InvalidArgumentException $e){}
 echo json_encode(['php_syntax'=>true,'mime_test'=>true,'header_injection_rejected'=>true]);
 }catch(Throwable $e){http_response_code(500);echo json_encode(['checks_failed'=>true,'reason'=>$e->getMessage()]);}
 '''.replace('IDENT',ident)
 put(probe,php.encode())
 req=urllib.request.Request('https://sonicproelectricite.ca/'+ident+'.php',headers={'X-Sonic-Automation-Key':key})
 with urllib.request.urlopen(req,timeout=60) as r:checks=json.load(r)
 if not all(checks.get(k) is True for k in ['php_syntax','mime_test','header_injection_rejected']):raise RuntimeError('PHP checks failed')
 # Helper first; the existing route is replaced only after all checks pass.
 for name,remote in zip(files,staged):
  f.rename(remote,private+'/calendar/'+name)
  if get(private+'/calendar/'+name)!=(stage/name).read_bytes():raise RuntimeError('Installed source mismatch')
 put(public+'/calendar-api/.htaccess',(stage/'calendar.htaccess').read_bytes())
 for headers,payload,expected in [({},b'{}',401),({'X-Sonic-Automation-Key':key},b'{"smtp_message_id":"bad"}',400)]:
  request=urllib.request.Request('https://sonicproelectricite.ca/calendar-api/mailbox-sent',data=payload,headers={**headers,'Content-Type':'application/json'})
  try:
   urllib.request.urlopen(request,timeout=30);raise RuntimeError('Invalid archive request accepted')
  except urllib.error.HTTPError as e:
   if e.code!=expected:raise
 print(json.dumps({'deployed':files,'checks':checks,'unauthenticated_rejected':True,'invalid_payload_rejected':True,'backup':str(backup)}))
finally:
 try:f.delete(probe)
 except ftplib.error_perm:pass
 for remote in staged:
  try:f.delete(remote)
  except ftplib.error_perm:pass
 f.quit()
