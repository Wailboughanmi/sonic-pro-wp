<?php
$result=isset($_GET['contact']) && is_string($_GET['contact']) ? sanitize_key($_GET['contact']) : '';
$messages=['test'=>'Test local réussi. Aucun message n’a été envoyé et aucune donnée du formulaire n’a été conservée.','sent'=>'Votre demande a été transmise au système d’envoi. Notre équipe pourra vous répondre.','failed'=>'L’envoi a échoué. Veuillez nous appeler.','unavailable'=>'Le formulaire n’est pas encore connecté. Veuillez nous appeler.'];
if (isset($messages[$result])) { echo '<div class="result" role="status" data-contact-result="'.esc_attr($result).'">'.esc_html($messages[$result]).'</div>'; }
?>
<form data-sonic-contact-form data-nonce-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" class="content" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="sonic_contact"><input type="hidden" name="sonic_language" value="<?php echo sonic_is_english()?'en':'fr'; ?>"><?php wp_nonce_field('sonic_contact','sonic_nonce'); ?><div class="form-grid"><label>Nom *<input name="name" autocomplete="name" maxlength="160" required></label><label>Courriel *<input name="email" type="email" autocomplete="email" maxlength="254" required></label><label class="full">Téléphone<input name="phone" type="tel" autocomplete="tel" maxlength="40"></label><label class="full">Votre projet *<textarea name="message" minlength="10" maxlength="5000" required aria-describedby="message-help"></textarea><span id="message-help">De 10 à 5 000 caractères. N’incluez pas d’information sensible.</span></label><label class="honeypot" aria-hidden="true">Site web<input name="website" tabindex="-1" autocomplete="off"></label><label class="full"><input name="consent" type="checkbox" value="1" required style="width:auto"> J’accepte l’utilisation de ces informations pour répondre à ma demande. <a href="<?php echo sonic_url('politique-de-confidentialite'); ?>">Confidentialité</a></label><div class="full"><p data-contact-status role="status" aria-live="polite" hidden></p><button type="submit"><?php echo wp_get_environment_type()==='local'?'Tester le formulaire':'Envoyer ma demande'; ?> ↗︎</button></div></div></form>

<script>
(function () {
  'use strict';
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
