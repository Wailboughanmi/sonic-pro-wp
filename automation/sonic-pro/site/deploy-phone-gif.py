"""Publish only the versioned, self-hosted email GIF using verified FTPS."""
from pathlib import Path
import ftplib,ssl,json,io,hashlib,urllib.request

root=Path('/Users/kingkong/Documents/workspace/sonic-pro-prod/rebuild')
asset=Path(__file__).resolve().parent/'assets/ringing-phone-red-v1.gif'
data=asset.read_bytes()
if not data.startswith(b'GIF89a') or len(data)>100000:raise SystemExit('Unexpected GIF asset')
credentials=json.loads((root/'secrets/hostinger-ftp.json').read_text())
f=ftplib.FTP_TLS(context=ssl.create_default_context(),timeout=60)
f.connect('82.25.87.142',21);f.host='hstgr.io';f.auth();f.login('u714692902',credentials['password']);f.prot_p();del credentials
folder='/domains/sonicproelectricite.ca/public_html/wp-content/uploads/sonic-automation'
try:f.mkd(folder)
except ftplib.error_perm as e:
 if not str(e).startswith('550'):raise
f.cwd(folder)
def get(name):
 b=io.BytesIO();f.retrbinary('RETR '+name,b.write);return b.getvalue()
try:
 old=get(asset.name)
 if old!=data:raise SystemExit('Versioned asset already exists with different contents; choose a new filename')
except ftplib.error_perm as e:
 if not str(e).startswith('550'):raise
 f.storbinary('STOR '+asset.name+'.stage',io.BytesIO(data))
 if get(asset.name+'.stage')!=data:raise SystemExit('Staged asset verification failed')
 f.rename(asset.name+'.stage',asset.name)
if get(asset.name)!=data:raise SystemExit('Deployed asset verification failed')
f.quit()
url='https://sonicproelectricite.ca/wp-content/uploads/sonic-automation/'+asset.name
with urllib.request.urlopen(url,timeout=45) as response:
 public=response.read();mime=response.headers.get_content_type()
if public!=data or mime!='image/gif':raise SystemExit('Public GIF verification failed')
print(json.dumps({'url':url,'content_type':mime,'bytes':len(data),'sha256':hashlib.sha256(data).hexdigest(),'public_readback_matches':True},indent=2))
