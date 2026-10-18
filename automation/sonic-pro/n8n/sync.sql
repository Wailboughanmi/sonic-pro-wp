WITH data AS (SELECT $1::jsonb AS body),
incoming_clicks AS (
 SELECT c.click_id,c.token,c.clicked_at FROM data,jsonb_to_recordset(body->'clicks') AS c(click_id text,token text,clicked_at timestamptz)
 JOIN public.sonicpro_calendar_links l ON l.token=c.token
 WHERE c.click_id ~ '^[a-f0-9]{32}$' AND c.clicked_at>=l.created_at AND c.clicked_at<=now()+interval '5 minutes'
),new_clicks AS (
 INSERT INTO public.sonicpro_calendar_clicks(click_id,token,clicked_at) SELECT * FROM incoming_clicks
 ON CONFLICT(click_id) DO NOTHING RETURNING *
),all_clicks AS (
 SELECT token,clicked_at FROM public.sonicpro_calendar_clicks UNION ALL SELECT token,clicked_at FROM new_clicks
),incoming_events AS (
 SELECT e.* FROM data,jsonb_to_recordset(body->'bookings') AS e(event_id text,status text,source text,event_created_at timestamptz,event_updated_at timestamptz,starts_at timestamptz,ends_at timestamptz,attendee_emails jsonb)
 WHERE e.event_id IS NOT NULL AND e.status IN ('confirmed','tentative','cancelled') AND e.event_updated_at IS NOT NULL
),attributed AS (
 SELECT e.*,a.token FROM incoming_events e LEFT JOIN LATERAL (
  SELECT l.token FROM public.sonicpro_calendar_links l
  JOIN public.sonicpro_hostinger_messages m USING(workflow_id,mailbox,message_id)
  JOIN all_clicks c ON c.token=l.token
  WHERE e.attendee_emails ? lower(m.customer_email) AND m.reply_sent_at IS NOT NULL
  AND e.event_created_at>=m.reply_sent_at AND c.clicked_at<=e.event_created_at
  AND c.clicked_at>=e.event_created_at-interval '30 days'
  ORDER BY c.clicked_at DESC,l.created_at DESC,l.token LIMIT 1
 ) a ON true
),saved AS (
 INSERT INTO public.sonicpro_calendar_bookings(event_id,status,source,event_created_at,event_updated_at,starts_at,ends_at,attendee_emails,link_token,attribution_method)
 SELECT event_id,status,source,coalesce(event_created_at,event_updated_at),event_updated_at,starts_at,ends_at,coalesce(attendee_emails,'[]'),token,CASE WHEN token IS NULL THEN 'unattributed' ELSE 'attendee_email_after_link_click_30d' END FROM attributed
 ON CONFLICT(event_id) DO UPDATE SET status=EXCLUDED.status,event_updated_at=EXCLUDED.event_updated_at,
 starts_at=coalesce(EXCLUDED.starts_at,sonicpro_calendar_bookings.starts_at),ends_at=coalesce(EXCLUDED.ends_at,sonicpro_calendar_bookings.ends_at),
 attendee_emails=CASE WHEN EXCLUDED.attendee_emails='[]'::jsonb THEN sonicpro_calendar_bookings.attendee_emails ELSE EXCLUDED.attendee_emails END,
 link_token=coalesce(EXCLUDED.link_token,sonicpro_calendar_bookings.link_token),
 attribution_method=CASE WHEN EXCLUDED.link_token IS NULL THEN sonicpro_calendar_bookings.attribution_method ELSE EXCLUDED.attribution_method END,synced_at=now()
 WHERE EXCLUDED.event_updated_at>=sonicpro_calendar_bookings.event_updated_at
 RETURNING event_id
),health AS (
 INSERT INTO public.sonicpro_integration_health(integration,last_success_at) VALUES('calendar_analytics',now())
 ON CONFLICT(integration) DO UPDATE SET last_success_at=EXCLUDED.last_success_at RETURNING last_success_at
)
SELECT (SELECT count(*) FROM new_clicks) AS new_clicks,(SELECT count(*) FROM saved) AS synchronized_events,(SELECT last_success_at FROM health) AS last_success_at;
