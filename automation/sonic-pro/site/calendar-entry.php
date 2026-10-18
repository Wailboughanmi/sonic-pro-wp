<?php
/** public_html/calendar-api/index.php. Application and credentials are outside the web root. */
$private=dirname(__DIR__,2).'/sonic-private';
if(!is_file($private.'/calendar-env.php')) {http_response_code(503);header('Content-Type: application/json');echo '{"error":"Calendar is not configured."}';exit;}
require $private.'/calendar-env.php';
putenv('CALENDAR_SECRETS='.$private.'/credentials');putenv('CALENDAR_RUNTIME='.$private.'/runtime');putenv('CALENDAR_TRUST_PROXY=0');
ini_set('session.save_path',$private.'/runtime');
require $private.'/calendar/router.php';
