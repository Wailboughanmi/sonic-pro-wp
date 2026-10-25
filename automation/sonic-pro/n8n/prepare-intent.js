const s=$('Clean-full-message').item.json;
const full=String(s.text||'').replace(/\r\n?/g,'\n').replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F]/g,'').trim();
const customerRequest=String(s.contact?.requestText||'').trim();
const screen=screenElectricalRequest(customerRequest);
const envelopeInjection=/(?:ignore|disregard|override)\s+(?:(?:all|any|the|your)\s+)*(?:previous|prior|system|instructions|rules)|system\s*prompt|<\|(?:im_start|system)|\[INST\]/i.test(String(s.subject||'')+'\n'+full);
const reason=s.contactIssue||(!s.websiteForm?'Unexpected website source markers':s.automatic?'Automatic/bulk website message':envelopeInjection?'Potential prompt injection':!full?'Empty message':full.length>4500?'Complete message exceeds model budget':!customerRequest?'Missing customer request':screen.reason);
return [{json:{subject:s.subject,customerRequest,languageHint:requestLanguage(customerRequest),fullReviewText:full,canCheckFullMessage:!reason,precheckReason:reason,messageId:s.messageId},pairedItem:{item:0}}];
