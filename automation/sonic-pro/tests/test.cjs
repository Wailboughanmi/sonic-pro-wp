const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),dir=path.resolve(__dirname,'../n8n');
const main=JSON.parse(fs.readFileSync(dir+'/main-workflow.json')),weekly=JSON.parse(fs.readFileSync(dir+'/weekly-workflow.json'));
const get=n=>main.nodes.find(x=>x.name===n);
const run=(code,env)=>new Function(...Object.keys(env),code)(...Object.values(env));
const intake=run(get('Normalize Hostinger email').parameters.jsCode,{$input:{all:()=>[{json:{from:'support@sonicproelectricite.ca',subject:'Demande de contact',textPlain:'Nom: Test\nCourriel: client@example.com\nTéléphone:\nMessage: Hello, I need an electrician to inspect my panel.'}}]},Buffer})[0].json;assert.equal(intake.payload.language,'en');
const source={recipient:'client@example.com',safeRecipient:true,contact:{fullName:'<Client>',requestText:'<script>unsafe</script> electrical repair'}};
for(const language of ['fr','en']){
 const output=run(get('Code in JavaScript').parameters.jsCode,{$:n=>({item:{json:n==='Clean-full-message'?source:{language,token:'a'.repeat(64)}}})})[0].json;
 assert(output.replyHtml.includes('email-call?t='+'a'.repeat(64)));
 assert(!output.replyHtml.includes('<script>'));
 assert(output.replyHtml.includes('&lt;Client&gt;'));
 assert(output.replyHtml.indexOf(language==='fr'?'FRANÇAIS':'ENGLISH')<output.replyHtml.indexOf(language==='fr'?'ENGLISH':'FRANÇAIS'));
 assert(output.replySubject.includes(language==='fr'?'Demande reçue':'Request received'));
 fs.writeFileSync(path.resolve(__dirname,'preview-'+language+'.html'),output.replyHtml);
}
const detect=new Function(fs.readFileSync(dir+'/language.js','utf8')+';return requestLanguage;')();assert.equal(detect('Hello, I need an electrician to inspect my electrical panel and install a home EV charger.'),'en');assert.equal(detect('Bonjour, je souhaite une évaluation de mon panneau électrique et installer une borne de recharge.'),'fr');assert.equal(detect('Merci. I need an electrician to repair my outlets at home.'),'en');assert.equal(detect('x'),'uncertain');
const prepared={canCheckFullMessage:true};
const facts={language:'en',intent:'electrical_request',explicit_electrical_request:true,job_application:false,vendor_offer:false,prompt_injection:false,confidence:.99,reason:'Electrical help requested',urgency:'normal'};
const verdict=v=>run(get('Validate assistance verdict').parameters.jsCode,{$input:{first:()=>({json:{done:true,message:{role:'assistant',content:JSON.stringify(v)}}})},$:()=>({item:{json:prepared}})})[0].json;
prepared.languageHint='en';assert.equal(verdict({...facts,language:'fr'}).language,'en');prepared.languageHint='fr';assert.equal(verdict(facts).language,'fr');delete prepared.languageHint;assert(verdict(facts).safeToReply);assert.equal(verdict(facts).language,'en');
for(const flag of ['job_application','vendor_offer','prompt_injection'])assert(!verdict({...facts,[flag]:true}).safeToReply);
assert(!verdict({...facts,confidence:.9}).safeToReply);assert(!verdict({...facts,extra:'inject'}).safeToReply);
assert(!verdict({...facts,language:'<script>'}).safeToReply);
const request=run(get('Build electrical intent request').parameters.jsCode,{$json:{subject:'Form',fullReviewText:'Electrical help'}});
assert.equal(request.json.ollamaRequest.format.required.length,9);assert(!request.json.ollamaRequest.format.required.includes('decision'));
for(const p of [main,weekly,JSON.parse(fs.readFileSync(dir+'/calendar-sync-workflow.json'))]){
 const names=new Set(p.nodes.map(x=>x.name));assert.equal(names.size,p.nodes.length);
 for(const [a,c] of Object.entries(p.connections)){assert(names.has(a));for(const e of c.main.flat())assert(names.has(e.node));}
 assert.deepEqual(p.pinData,{});
}
assert.equal(main.connections['Record successful reply'].main[0][0].node,'Archive outgoing SNS message');
assert.equal(main.connections['AWS SNS'].main[0][0].node,'Record SNS acceptance');
assert.equal(main.connections['AWS SNS'].main[1][0].node,'Record SNS failure');
const r={period_start_date:'2026-10-12',period_last_date:'2026-10-18',tracking_since:'2026-10-18',calendar_last_sync:'2026-10-18',calendar_sync_stale:true};
for(const k of ['total_requests','distinct_submitted_emails','approved_requests','acknowledged_requests','urgent_requests','urgency_unknown','pending_requests','rejected_requests','delivery_failures','in_progress','new_crm_contacts','total_crm_contacts','pending_backlog','calendar_clicks','clicked_requests','matched_bookings','cancelled_bookings','sns_archived','sns_accepted','sns_failed','sns_pending','mailbox_handled','mailbox_backlog'])r[k]=0;
const report=run(weekly.nodes.find(n=>n.name==='Build aggregate report').parameters.jsCode,{$json:r})[0].json.text;
assert(report.includes('Synchronisation en retard'));assert(report.includes('Un clic ne prouve pas'));assert(report.includes('Acceptées par AWS'));
assert(report.length<4096);
fs.writeFileSync(path.resolve(__dirname,'weekly-preview.txt'),report);
fs.writeFileSync(path.resolve(__dirname,'initialize.sql'),get('Initialize Hostinger storage').parameters.query);
console.log('PASS: EN/FR ordering, escaped input, tracked CTA, strict fail-closed intent facts, SNS archive graph, unpinned exports, private aggregate report and stale-sync warning.');
