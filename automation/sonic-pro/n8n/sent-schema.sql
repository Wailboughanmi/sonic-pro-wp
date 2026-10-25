CREATE TABLE IF NOT EXISTS public.sonicpro_sent_email_copies (
 archive_id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
 workflow_id text NOT NULL, mailbox text NOT NULL, message_id text NOT NULL,
 execution_id text NOT NULL, smtp_message_id text NOT NULL UNIQUE,
 recipient text NOT NULL, subject text NOT NULL, html_body text NOT NULL,
 smtp_response text NOT NULL, sent_at timestamptz NOT NULL DEFAULT now(),
 state text NOT NULL DEFAULT 'pending' CHECK(state IN ('pending','archived','failed')),
 attempts integer NOT NULL DEFAULT 0, claimed_until timestamptz,
 archived_at timestamptz, sent_folder text, imap_uid text, error_detail text,
 UNIQUE(workflow_id,mailbox,message_id),
 FOREIGN KEY(workflow_id,mailbox,message_id) REFERENCES public.sonicpro_hostinger_messages(workflow_id,mailbox,message_id)
);
