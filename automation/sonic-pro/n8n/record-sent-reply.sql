WITH sent AS (
 UPDATE public.sonicpro_hostinger_messages
 SET state='processed',category='Intervention_Depuis_site_web',reply_sent_at=now(),updated_at=now()
 WHERE workflow_id=$1 AND mailbox=$2 AND message_id=$3 AND state='sending'
 RETURNING workflow_id,mailbox,message_id,reply_sent_at
)
INSERT INTO public.sonicpro_sent_email_copies
 (workflow_id,mailbox,message_id,execution_id,smtp_message_id,recipient,subject,html_body,smtp_response,sent_at,attempts)
SELECT workflow_id,mailbox,message_id,$4,$5,$6,$7,$8,$9,reply_sent_at,1 FROM sent
ON CONFLICT(workflow_id,mailbox,message_id) DO UPDATE SET message_id=sonicpro_sent_email_copies.message_id
RETURNING archive_id,message_id,smtp_message_id,recipient,subject,html_body,sent_at;
