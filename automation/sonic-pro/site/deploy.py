"""Deploy reviewed tracking routes with verified FTPS, source checks and backups."""
from pathlib import Path
import ftplib,ssl,json,io,hashlib,time,os,secrets,urllib.request,urllib.error
os.umask(0o077)
stage=Path(__file__).resolve().parent
root=Path('/Users/kingkong/Documents/workspace/sonic-pro-prod/rebuild')
private='/domains/sonicproelectricite.ca/sonic-private'
public='/domains/sonicproelectricite.ca/public_html'
expected=hashlib.sha256((root/'calendar/router.php').read_bytes()).hexdigest()
expected_rules=hashlib.sha256((root/'deploy/calendar.htaccess').read_bytes()).hexdigest()
keyfile=Path('/private/tmp/sonic-pro-calendar-analytics.key')
if not keyfile.exists():keyfile.write_text(secrets.token_hex(32));keyfile.chmod(0o600)
key=keyfile.read_text().strip()
credentials=json.loads((root/'secrets/hostinger-ftp.json').read_text())
f=ftplib.FTP_TLS(context=ssl.create_default_context(),timeout=60)
f.connect('82.25.87.142',21);f.host='hstgr.io';f.auth();f.login('u714692902',credentials['password']);f.prot_p();del credentials
def get(path):
 b=io.BytesIO();f.retrbinary('RETR '+path,b.write);return b.getvalue()
def put(path,data):f.storbinary('STOR '+path,io.BytesIO(data))
original=get(private+'/calendar/router.php')
old_rules=get(public+'/calendar-api/.htaccess')
if hashlib.sha256(old_rules).hexdigest()!=expected_rules:raise SystemExit('Live rewrite rules changed; stopped')
if hashlib.sha256(original).hexdigest()!=expected:raise SystemExit('Live router differs; no changes')
ident='booking-analytics-'+str(time.time_ns())
backup=root/'backups'/ident;backup.mkdir(parents=True,mode=0o700);(backup/'router.php').write_bytes(original)
try:
 existing=get(private+'/credentials/automation-reporting-key').decode().strip()
 if existing!=key:raise SystemExit('Existing export key differs; stopped')
except ftplib.error_perm as e:
 if not str(e).startswith('550'):raise
 put(private+'/credentials/automation-reporting-key',key.encode())
for name in ['mailbox-processed.php','automation-tracking.php','router.php']:
 data=(stage/name).read_bytes();temp=private+'/calendar/'+ident+'-'+name
 put(temp,data)
 if get(temp)!=data:raise SystemExit('Staging verification failed')
 f.rename(temp,private+'/calendar/'+name)
 if get(private+'/calendar/'+name)!=data:raise SystemExit('Readback verification failed')
 (root/'calendar'/name).write_bytes(data)
(backup/'calendar.htaccess').write_bytes(old_rules)
new_rules=(stage/'calendar.htaccess').read_bytes()
put(public+'/calendar-api/.htaccess',new_rules)
if get(public+'/calendar-api/.htaccess')!=new_rules:raise SystemExit('Rewrite rules readback failed')
(root/'deploy/calendar.htaccess').write_bytes(new_rules)
f.quit()
url='https://sonicproelectricite.ca/calendar-api/automation-events'
try:
 urllib.request.urlopen(url,timeout=30)
 raise SystemExit('Unauthenticated export unexpectedly accepted')
except urllib.error.HTTPError as e:
 if e.code!=401:raise
req=urllib.request.Request(url,headers={'X-Sonic-Automation-Key':key})
with urllib.request.urlopen(req,timeout=120) as r:export=json.load(r)
output={'backup':str(backup),'files_verified':['calendar/router.php','calendar/automation-tracking.php','calendar/mailbox-processed.php','calendar-api/.htaccess'],'unauthenticated_export_status':401,'authenticated_export_ok':isinstance(export.get('clicks'),list) and isinstance(export.get('bookings'),list),'lookback_days':export.get('lookback_days'),'exported_calendar_events':len(export.get('bookings',[]))}
(stage/'deployment-result.json').write_text(json.dumps(output,indent=2)+'\n')
print(json.dumps(output,indent=2))
