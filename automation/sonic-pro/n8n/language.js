// Use only the customer's request, never French/English form labels or contact fields.
function requestLanguage(text) {
 const words=new Set(String(text||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().match(/[a-z]+/g)||[]);
 const fr=['bonjour','merci','besoin','electricien','electrique','electriques','panneau','prises','travaux','devis','depannage','reparation','souhaite','voudrais','pouvez','borne','recharge','je','nous','vous','mon','ma','mes','pour'];
 const en=['hello','hi','thanks','please','need','electrician','electrical','outlet','outlets','panel','repair','quote','estimate','wiring','charger','could','would','help','i','my','we','our','your','home'];
 const score=list=>list.filter(w=>words.has(w)).length;
 const f=score(fr),e=score(en);
 return e>=2&&e>f?'en':f>=2&&f>e?'fr':'uncertain';
}
