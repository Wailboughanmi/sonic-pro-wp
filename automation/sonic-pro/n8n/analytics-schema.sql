ALTER TABLE public.sonicpro_hostinger_messages ADD COLUMN IF NOT EXISTS mailbox_processed_at timestamptz,ADD COLUMN IF NOT EXISTS mailbox_mark_attempts integer NOT NULL DEFAULT 0,ADD COLUMN IF NOT EXISTS mailbox_mark_error text;
ALTER TABLE public.sonicpro_hostinger_messages ADD COLUMN IF NOT EXISTS language text NOT NULL DEFAULT 'fr';
CREATE TABLE IF NOT EXISTS public.sonicpro_calendar_links (
 token text PRIMARY KEY CHECK (token ~ '^[a-f0-9]{64}$'),
 workflow_id text NOT NULL, mailbox text NOT NULL,message_id text NOT NULL,
 created_at timestamptz NOT NULL DEFAULT now(),
 UNIQUE(workflow_id,mailbox,message_id),
 FOREIGN KEY(workflow_id,mailbox,message_id) REFERENCES public.sonicpro_hostinger_messages(workflow_id,mailbox,message_id)
);
CREATE TABLE IF NOT EXISTS public.sonicpro_calendar_clicks (
 click_id text PRIMARY KEY,token text NOT NULL REFERENCES public.sonicpro_calendar_links(token),
 clicked_at timestamptz NOT NULL,recorded_at timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX IF NOT EXISTS sonicpro_click_time ON public.sonicpro_calendar_clicks(clicked_at,token);
CREATE TABLE IF NOT EXISTS public.sonicpro_calendar_bookings (
 event_id text PRIMARY KEY,status text NOT NULL,source text NOT NULL,
 event_created_at timestamptz NOT NULL,event_updated_at timestamptz NOT NULL,
 starts_at timestamptz,ends_at timestamptz,attendee_emails jsonb NOT NULL DEFAULT '[]',
 link_token text REFERENCES public.sonicpro_calendar_links(token),
 attribution_method text NOT NULL DEFAULT 'unattributed',
 first_seen_at timestamptz NOT NULL DEFAULT now(),synced_at timestamptz NOT NULL DEFAULT now()
);
CREATE TABLE IF NOT EXISTS public.sonicpro_sns_notifications (
 notification_id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
 workflow_id text NOT NULL,mailbox text NOT NULL,message_id text NOT NULL,
 execution_id text NOT NULL,topic_arn text NOT NULL,subject text NOT NULL,message_body text NOT NULL,
 state text NOT NULL DEFAULT 'pending' CHECK(state IN ('pending','accepted','failed')),
 aws_message_id text,error_detail text,created_at timestamptz NOT NULL DEFAULT now(),accepted_at timestamptz,
 UNIQUE(workflow_id,mailbox,message_id,execution_id),
 FOREIGN KEY(workflow_id,mailbox,message_id) REFERENCES public.sonicpro_hostinger_messages(workflow_id,mailbox,message_id)
);
CREATE INDEX IF NOT EXISTS sonicpro_sns_created ON public.sonicpro_sns_notifications(created_at,state);
CREATE TABLE IF NOT EXISTS public.sonicpro_integration_health (integration text PRIMARY KEY,last_success_at timestamptz NOT NULL);
SELECT 'Calendar tracking and SNS archive ready' AS status;
