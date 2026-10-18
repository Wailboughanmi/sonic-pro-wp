"""Verify and deploy only the two reviewed PHP helpers; no credentials in output."""
from pathlib import Path
import ftplib,ssl,json,io,hashlib,time,os
os.umask(0o077)
stage=Path(__file__).resolve().parent;root=Path('/Users/kingkong/Documents/workspace/sonic-pro-prod/rebuild')
private='/domains/sonicproelectricite.ca/sonic-private'
credentials=json.loads((root/'secrets/hostinger-ftp.json').read_text())
f=ftplib.FTP_TLS(context=ssl.create_default_context(),timeout=60);f.connect('82.25.87.142',21);f.host='hstgr.io';f.auth();f.login('u714692902',credentials['password']);f.prot_p();del credentials
def get(path):
 b=io.BytesIO();f.retrbinary('RETR '+path,b.write);return b.getvalue()
backup=root/'backups'/('analytics-patch-'+str(time.time_ns()));backup.mkdir(parents=True,mode=0o700)
for name in ['automation-tracking.php','mailbox-processed.php']:
 remote=private+'/calendar/'+name;old=get(remote)
 if old!=(root/'calendar'/name).read_bytes():raise SystemExit('Live helper changed; stopped')
 (backup/name).write_bytes(old);data=(stage/name).read_bytes();temp=remote+'.stage'
 f.storbinary('STOR '+temp,io.BytesIO(data))
 if get(temp)!=data:raise SystemExit('Staging mismatch')
 f.rename(temp,remote)
 if get(remote)!=data:raise SystemExit('Readback mismatch')
 (root/'calendar'/name).write_bytes(data)
f.quit();print('Two reviewed helpers deployed and verified. Backup: '+str(backup))
