// Conservative checks on customer prose only. A failed check requires human review.
function requestTextKey(text) {
 return String(text||'').normalize('NFKD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/\s+/g,' ').trim();
}
function screenElectricalRequest(text) {
 const t=requestTextKey(text);
 const injection=/(?:ignore|disregard|override)\s+(?:(?:all|any|the|your)\s+)*(?:previous|prior|system|instructions|rules)|ignor(?:er|ez|e)?\s+(?:(?:les|toutes|vos)\s+)*(?:instructions|regles)|system\s*prompt|<\|(?:im_start|system)|\[inst\]|classif(?:y|ier|iez|ie)\s+(?:this|ce|cet)|reveal\s+(?:the\s+)?(?:secret|token|password)/i.test(t);
 const linked=/https?:\/\/|www\.|<a\s|(?:t\.me|telegra\.ph)\//i.test(t);
 const employment=/\b(?:curriculum|resume|internship|internships|candidature|emploi|embauche|stage|stages|recrutement)\b|\b(?:looking|searching|applying|apply)\b.{0,50}\b(?:job|position|work for|work with)\b|\b(?:job|employment)\s+(?:application|opportunit)/i.test(t);
 const vendor=/\b(?:we|i)\s+(?:offer|provide|speciali[sz]e|deliver)\b|\b(?:our|my)\s+(?:company|services|turnaround|rates|clients)\b|\b(?:we|i)\s+(?:will|can)\s+(?:provide|offer)\b|\b(?:nous|je)\s+(?:offrons|proposons|fournissons|offre|propose|fournis)\b|\b(?:nos|mes)\s+(?:services|tarifs|prestations)\b|\b(?:provides?|offering|offers?)\b.{0,80}\b(?:services|takeoffs?|estimating|marketing|seo|web design|it support)\b|\b(?:quantity takeoffs?|quantity breakdowns?|long.term professional relationship)\b/i.test(t);
 const scam=/\b(?:earn|gagnez|gagner|revenus?|income|profits?|uncover gems|crypto|backlinks?)\b.{0,100}(?:\$|\beuros?\b|\bmillion|\bmonth|\bmois|\bdaily|\bjour)|\b(?:make money|easy money|argent facile|read the news and)\b/i.test(t);
 const context=/\b(?:electrical|electrician|electricians|electric|electricity|electricien|electricienne|electriciens|electrique|electriques|electricite|panneau|panneaux|panel|panels|breaker|breakers|disjoncteur|disjoncteurs|fusible|fusibles|plombs|wiring|rewiring|cablage|filage|circuit|circuits|outlet|outlets|socket|sockets|prise|prises|power|courant|lighting|lights?|luminaire|luminaires|lumiere|lumieres|thermostat|thermostats|baseboard|plinthe|plinthes|chauffage|heating|borne|recharge|charger|grounding|surtension|short circuit|court.circuit)\b|\b(?:120|125|200|240)\s*(?:v|volt|volts|amp|ampere|amperes)\b/i.test(t);
 const reason=injection?'Potential prompt injection':linked?'Customer message contains an untrusted external link':employment?'Employment application or recruiting':vendor?'Vendor offering services to Sonic Pro':scam?'Promotional or get-rich message':!context?'No explicit electrical need in customer message':'';
 return {eligible:!reason,reason};
}
