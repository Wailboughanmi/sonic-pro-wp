"""Deploy only the contact confirmation popup, with verified FTPS and backup."""
from pathlib import Path
import ftplib,ssl,json,io,time,os,urllib.request,urllib.error
os.umask(0o077)
stage=Path(__file__).resolve().parent/'wordpress/theme/parts/contact.php'
root=Path('/Users/kingkong/Documents/workspace/sonic-pro-prod/rebuild')
local=root/'theme/parts/contact.php'
public='/domains/sonicproelectricite.ca/public_html'
private='/domains/sonicproelectricite.ca/sonic-private'
remote=public+'/wp-content/themes/sonic-rebuild/parts/contact.php'
original=local.read_bytes();data=stage.read_bytes()
credentials=json.loads((root/'secrets/hostinger-ftp.json').read_text())
f=ftplib.FTP_TLS(context=ssl.create_default_context(),timeout=60)
f.connect('82.25.87.142',21);f.host='hstgr.io';f.auth();f.login('u714692902',credentials['password']);f.prot_p();del credentials
def get(path):
 b=io.BytesIO();f.retrbinary('RETR '+path,b.write);return b.getvalue()
def put(path,data):f.storbinary('STOR '+path,io.BytesIO(data))
if get(remote)!=original:raise SystemExit('Live contact template changed; nothing deployed')
ident='contact-confirmation-'+str(time.time_ns())
backup=root/'backups'/ident;backup.mkdir(parents=True,mode=0o700);(backup/'contact.php').write_bytes(original)
temp=private+'/'+ident+'.php';put(temp,data)
if get(temp)!=data:raise SystemExit('Staging verification failed')
f.rename(temp,remote)
if get(remote)!=data:raise SystemExit('Live readback verification failed')
local.write_bytes(data)
hook=public+'/wp-content/mu-plugins/sonic-contact-confirmation-once.php'
marker=private+'/'+ident+'.json'
php="""<?php
add_action('wp_loaded',static function(){
 $marker=dirname(ABSPATH).'/sonic-private/IDENT.json';
 if(file_exists($marker))return;
 do_action('litespeed_purge_url',home_url('/contactez-nous/'));
 do_action('litespeed_purge_url',home_url('/en/contactez-nous/'));
 file_put_contents($marker,json_encode(['purge_requested'=>true,'at'=>time()]));chmod($marker,0600);
});
""".replace('IDENT',ident)
put(hook,php.encode())
try:
 try:urllib.request.urlopen('https://sonicproelectricite.ca/wp-login.php?contact_confirmation='+ident,timeout=60).read()
 except urllib.error.HTTPError:pass
 purge=json.loads(get(marker))
finally:f.delete(hook)
f.quit()
results={}
for path,title in [('/contactez-nous/?contact=sent','Demande envoyée'),('/en/contactez-nous/?contact=sent','Request sent')]:
 with urllib.request.urlopen('https://sonicproelectricite.ca'+path,timeout=45) as response:body=response.read().decode()
 results[path]={'confirmation_markup': 'class="sonic-contact-confirmation"' in body,'localized_title':title in body,'modal_activation':'confirmation.showModal()' in body}
 if not all(results[path].values()):raise SystemExit('Public confirmation verification failed: '+path)
print(json.dumps({'backup':str(backup),'template_readback_matches':True,'cache':purge,'pages':results,'emails_sent_during_test':0},indent=2))
