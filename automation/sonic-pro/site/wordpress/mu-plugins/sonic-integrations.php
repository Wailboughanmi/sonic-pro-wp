<?php
/** Site integrations: no external services are enabled by default. */
if (!defined('ABSPATH')) { exit; }
function sonic_marketing_enabled() {
    return wp_get_environment_type()==='production' && defined('SONIC_MARKETING_APPROVED') && SONIC_MARKETING_APPROVED;
}
add_action('wp_head', function () {
    if (!sonic_marketing_enabled()) { return; }
    ?>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-MMLZ6PPQ');</script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-16923388945"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','AW-16923388945');gtag('config','G-JSX12ZVXD5');</script>
    <meta name="google-site-verification" content="69K_bxQOpr8VwQZniyfBvTZ9y547jswn7x-kDVpCMJE">
    <?php
}, 5);
add_action('wp_body_open', function () {
    if (sonic_marketing_enabled()) { echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MMLZ6PPQ" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>'; }
});
function sonic_contact_handler() {
    $english=isset($_POST['sonic_language']) && $_POST['sonic_language']==='en';
    $translate=static function($text) use ($english) { return $english && function_exists('sonic_translate_english') ? sonic_translate_english($text) : $text; };
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die($translate('Méthode non permise.'), '', ['response'=>405]); }
    $nonce=isset($_POST['sonic_nonce']) && is_string($_POST['sonic_nonce']) ? sanitize_text_field(wp_unslash($_POST['sonic_nonce'])) : '';
    if (!wp_verify_nonce($nonce,'sonic_contact')) { wp_die($translate('Session expirée. Rechargez le formulaire.'), '', ['response'=>403,'back_link'=>true]); }
    $key='sonic_rate_'.hash_hmac('sha256',$_SERVER['REMOTE_ADDR']??'unknown',wp_salt());
    $count=(int)get_transient($key);
    if ($count>=5) { wp_die($translate('Trop de demandes. Veuillez réessayer dans 15 minutes ou nous appeler.'), '', ['response'=>429]); }
    set_transient($key,$count+1,15*MINUTE_IN_SECONDS);
    foreach (['name','email','phone','message','website'] as $field) {
        if (!isset($_POST[$field]) || !is_string($_POST[$field])) { wp_die($translate('Formulaire invalide.'), '', ['response'=>400]); }
    }
    $name=sanitize_text_field(wp_unslash($_POST['name']));
    $email=sanitize_email(wp_unslash($_POST['email']));
    $phone=sanitize_text_field(wp_unslash($_POST['phone']));
    $message=sanitize_textarea_field(wp_unslash($_POST['message']));
    if ($_POST['website']!=='' || !$name || strlen($name)>160 || !is_email($email) || strlen($email)>254 || strlen($message)<10 || strlen($message)>5000 || strlen($phone)>40 || empty($_POST['consent'])) {
        wp_die($translate('Vérifiez les champs requis et votre consentement. Le message doit contenir de 10 à 5 000 caractères. Revenez au formulaire pour corriger.'), '', ['response'=>400,'back_link'=>true]);
    }
    $recipient=apply_filters('sonic_contact_recipient',defined('SONIC_FORM_RECIPIENT') ? SONIC_FORM_RECIPIENT : '');
    if (wp_get_environment_type()==='local') { $result='test'; }
    elseif (is_email($recipient)) {
        $sent=wp_mail($recipient,'Demande de contact',"Nom: $name\nCourriel: $email\nTéléphone: $phone\n\nMessage: $message",['Reply-To: '.$email]);
        $result=$sent?'sent':'failed';
    } else { $result='unavailable'; }
    wp_safe_redirect(add_query_arg('contact',$result,home_url($english?'/en/contactez-nous/':'/contactez-nous/')),303); exit;
}
add_action('admin_post_nopriv_sonic_contact','sonic_contact_handler');
add_action('admin_post_sonic_contact','sonic_contact_handler');

/** Keep contact-page HTML and the refresh response out of page/CDN caches. */
function sonic_contact_no_cache() {
    if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
    if (!headers_sent()) { nocache_headers(); header('CDN-Cache-Control: no-store', true); }
    do_action('litespeed_control_set_nocache', 'Sonic-Pro contact nonce');
}
add_action('template_redirect', static function () {
    if (is_page('contactez-nous')) { sonic_contact_no_cache(); }
}, 0);

/** Public guest token, scoped to the current WordPress user/session. No credentials or form data. */
function sonic_contact_nonce_response() {
    sonic_contact_no_cache();
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
        wp_send_json_error(['code'=>'method_not_allowed'], 405);
    }
    wp_send_json_success(['nonce'=>wp_create_nonce('sonic_contact')]);
}
add_action('wp_ajax_nopriv_sonic_contact_nonce', 'sonic_contact_nonce_response');
add_action('wp_ajax_sonic_contact_nonce', 'sonic_contact_nonce_response');
