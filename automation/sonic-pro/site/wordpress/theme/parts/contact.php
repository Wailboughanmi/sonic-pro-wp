<?php
$result=isset($_GET['contact']) && is_string($_GET['contact']) ? sanitize_key($_GET['contact']) : '';
$messages=['test'=>'Test local réussi. Aucun message n’a été envoyé et aucune donnée du formulaire n’a été conservée.','sent'=>'Votre demande a été transmise au système d’envoi. Notre équipe pourra vous répondre.','failed'=>'L’envoi a échoué. Veuillez nous appeler.','unavailable'=>'Le formulaire n’est pas encore connecté. Veuillez nous appeler.'];
$show_confirmation=$result==='sent';
$confirmation_message=sonic_is_english()?'Your request has been submitted to our email system. Our team can now respond.':$messages['sent'];
if (isset($messages[$result]) && !$show_confirmation) { echo '<div class="result" role="status" data-contact-result="'.esc_attr($result).'">'.esc_html($messages[$result]).'</div>'; }
?>
<?php if ($show_confirmation): ?>
<style>
.sonic-contact-confirmation{position:fixed;inset:24px 0 auto;margin:0 auto;width:calc(100% - 32px);max-width:560px;max-height:calc(100dvh - 48px);overflow:auto;box-sizing:border-box;padding:24px;background:#fff;color:#17345c;border:1px solid #dbe5df;border-top:5px solid #23804d;border-radius:14px;box-shadow:0 18px 60px #10223b40;text-align:left;z-index:999999}
.sonic-contact-confirmation::backdrop{background:#10223b52}
.sonic-contact-confirmation__head{display:flex;align-items:center;gap:12px;margin-bottom:12px}
.sonic-contact-confirmation__icon{display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border-radius:50%;background:#e8f5ed;color:#23804d;font-size:26px;font-weight:700}
.sonic-contact-confirmation h2{margin:0;font-size:24px;line-height:1.3;color:#17345c}
.sonic-contact-confirmation p{margin:0 0 20px;font-size:16px;line-height:1.6;color:#475569}
.sonic-contact-confirmation button{display:inline-block;min-height:44px;padding:10px 22px;border:0;border-radius:7px;background:#17345c;color:#fff;font:inherit;font-weight:700;cursor:pointer}
.sonic-contact-confirmation button:focus-visible{outline:3px solid #c82736;outline-offset:3px}
@media(max-width:480px){.sonic-contact-confirmation{top:16px;padding:20px}.sonic-contact-confirmation h2{font-size:21px}}
</style>
<dialog class="sonic-contact-confirmation" data-sonic-contact-confirmation data-contact-result="sent" aria-labelledby="sonic-contact-confirmation-title" aria-describedby="sonic-contact-confirmation-message">
 <div class="sonic-contact-confirmation__head"><span class="sonic-contact-confirmation__icon" aria-hidden="true">✓</span><h2 id="sonic-contact-confirmation-title"><?php echo sonic_is_english()?'Request sent':'Demande envoyée'; ?></h2></div>
 <p id="sonic-contact-confirmation-message"><?php echo esc_html($confirmation_message); ?></p>
 <button type="button" data-close-contact-confirmation autofocus><?php echo sonic_is_english()?'Close':'Fermer'; ?></button>
</dialog>
<noscript><div class="result" role="status"><?php echo esc_html($confirmation_message); ?></div></noscript>
<?php endif; ?>
<form data-sonic-contact-form data-nonce-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" class="content" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="sonic_contact"><input type="hidden" name="sonic_language" value="<?php echo sonic_is_english()?'en':'fr'; ?>"><?php wp_nonce_field('sonic_contact','sonic_nonce'); ?><div class="form-grid"><label>Nom *<input name="name" autocomplete="name" maxlength="160" required></label><label>Courriel *<input name="email" type="email" autocomplete="email" maxlength="254" required></label><label class="full">Téléphone<input name="phone" type="tel" autocomplete="tel" maxlength="40"></label><label class="full">Votre projet *<textarea name="message" minlength="10" maxlength="5000" required aria-describedby="message-help"></textarea><span id="message-help">De 10 à 5 000 caractères. N’incluez pas d’information sensible.</span></label><label class="honeypot" aria-hidden="true">Site web<input name="website" tabindex="-1" autocomplete="off"></label><label class="full"><input name="consent" type="checkbox" value="1" required style="width:auto"> J’accepte l’utilisation de ces informations pour répondre à ma demande. <a href="<?php echo sonic_url('politique-de-confidentialite'); ?>">Confidentialité</a></label><div class="full"><p data-contact-status role="status" aria-live="polite" hidden></p><button type="submit"><?php echo wp_get_environment_type()==='local'?'Tester le formulaire':'Envoyer ma demande'; ?> ↗︎</button></div></div></form>

<script>
(function () {
  'use strict';
  const confirmation = document.querySelector('[data-sonic-contact-confirmation]');
  if (confirmation) {
    const close = confirmation.querySelector('[data-close-contact-confirmation]');
    close.addEventListener('click', function () {
      if (typeof confirmation.close === 'function') confirmation.close();
      else confirmation.removeAttribute('open');
    });
    if (typeof confirmation.showModal === 'function') confirmation.showModal();
    else confirmation.setAttribute('open', '');
    window.scrollTo({ top: 0, behavior: 'instant' });
    // Keep refresh/back from presenting the same old success as a new submission.
    const cleanUrl = new URL(window.location.href);
    cleanUrl.searchParams.delete('contact');
    window.history.replaceState(window.history.state, '', cleanUrl);
  }
  const form = document.currentScript.previousElementSibling;
  if (!form || !form.matches('[data-sonic-contact-form]')) return;
  const nonce = form.querySelector('[name="sonic_nonce"]');
  const button = form.querySelector('[type="submit"]');
  const status = form.querySelector('[data-contact-status]');
  const english = form.querySelector('[name="sonic_language"]').value === 'en';
  let sending = false;
  let ready = false;
  form.addEventListener('submit', async function (event) {
    if (ready) { ready = false; return; }
    event.preventDefault();
    if (sending || !form.reportValidity()) return;
    sending = true;
    button.disabled = true;
    status.hidden = false;
    status.textContent = english ? 'Sending your request…' : 'Envoi de votre demande…';
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 15000);
    try {
      const response = await fetch(form.dataset.nonceUrl + '?action=sonic_contact_nonce', {
        method: 'GET',
        credentials: 'same-origin',
        cache: 'no-store',
        signal: controller.signal,
        headers: { Accept: 'application/json' }
      });
      if (!response.ok) throw new Error('nonce_unavailable');
      const result = await response.json();
      if (!result.success || !/^[a-f0-9]{10}$/i.test(result.data?.nonce || '')) {
        throw new Error('nonce_invalid');
      }
      nonce.value = result.data.nonce;
      ready = true;
      // The normal POST and server-side nonce, honeypot and rate checks still apply.
      form.requestSubmit();
    } catch (_) {
      ready = false;
      sending = false;
      button.disabled = false;
      status.textContent = english
        ? 'Unable to send right now. Your text is still here. Please retry or call 514 318-3545.'
        : 'Impossible d’envoyer pour le moment. Votre texte est conservé. Réessayez ou appelez le 514 318-3545.';
    } finally {
      clearTimeout(timer);
    }
  });
}());

</script>
