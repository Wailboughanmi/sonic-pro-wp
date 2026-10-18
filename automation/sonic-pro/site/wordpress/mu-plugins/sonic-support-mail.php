<?php
/** Production SMTP settings are stored outside the document root. */
if (!defined('ABSPATH')) { exit; }
if (wp_get_environment_type() !== 'production') { return; }
$sonic_mail_path=dirname(ABSPATH).'/sonic-private/credentials/support-mail.json';
if (!is_readable($sonic_mail_path)) { return; }
$sonic_mail=json_decode(file_get_contents($sonic_mail_path),true);
if (!is_array($sonic_mail) || ($sonic_mail['host']??'')!=='smtp.hostinger.com' || ($sonic_mail['username']??'')!=='support@sonicproelectricite.ca' || empty($sonic_mail['password'])) { return; }
add_filter('sonic_contact_recipient', static function () { return 'support@sonicproelectricite.ca'; });
add_action('phpmailer_init', static function ($mailer) use ($sonic_mail) {
    $mailer->isSMTP();
    $mailer->Host='smtp.hostinger.com';
    $mailer->Port=465;
    $mailer->SMTPSecure='ssl';
    $mailer->SMTPAuth=true;
    $mailer->Username=$sonic_mail['username'];
    $mailer->Password=$sonic_mail['password'];
    $mailer->SMTPDebug=0;
    $mailer->setFrom('support@sonicproelectricite.ca','Sonic-Pro Électricité');
    $mailer->Sender='support@sonicproelectricite.ca';
}, PHP_INT_MAX);
