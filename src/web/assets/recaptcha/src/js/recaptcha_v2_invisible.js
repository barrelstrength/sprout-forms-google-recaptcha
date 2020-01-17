/**
 * Prepare reCAPTCHA for ALL Sprout Forms on page
 */
class SproutFormsGoogleRecaptchaInvisible {

  constructor(settings) {
    this.siteKey = settings.siteKey;
    this.size = settings.size ?? null;
    this.theme = settings.theme ?? null;
    this.badge = settings.badge ?? null;
    this.badgePosition = this.invisibleRecaptchaGetBadgePosition(this.badge);
    this.grecaptcha = settings.grecaptcha;

    this.initRecaptchas();
  }

  initRecaptchas() {
    let self = this;
    let sproutFormsRecaptchaContainers = document.querySelectorAll('.google-recaptcha-container');

    for (let recaptchaContainer of sproutFormsRecaptchaContainers) {
      let formId = recaptchaContainer.getAttribute('data-google-recaptcha-form-id');
      let form = document.getElementById(formId);
      this.addFormEventListener(form);

      let widgetId = this.grecaptcha.render(recaptchaContainer.id, {
        'sitekey': self.siteKey,
        'theme': self.theme,
        'badge': self.badgePosition,
        'size': 'invisible',
        'callback': function(token) {
          self.onSproutFormsRecaptchaSuccess(token, form)
        }
      });

      form.setAttribute('data-google-recaptcha-widget-id', widgetId);

      let submitButtonContainer = form.querySelector('.submit');
      let recaptchaInlineTextTerms = form.querySelector('.google-recaptcha-inline-text-terms');

      // Place the reCAPTCHA terms after the submit button
      if (this.badge === 'inline-text') {
        submitButtonContainer.appendChild(recaptchaInlineTextTerms);
        recaptchaInlineTextTerms.style.display = 'block';
      } else {
        submitButtonContainer.appendChild(recaptchaContainer);
        recaptchaContainer.style.display = 'block';
      }
    }
  }

  /**
   * Adds Event Listener to Form submit button to ensure reCAPTCHA gets processed
   *
   * @param form
   */
  addFormEventListener(form) {
    let self = this;

    form.addEventListener('submit', function(event) {
      let targetForm = event.target;
      let widgetId = targetForm.getAttribute('data-google-recaptcha-widget-id');

      // Make sure to run reCAPTCHA before submitting form
      if (!self.grecaptcha.getResponse(widgetId)) {
        event.preventDefault();
        self.grecaptcha.execute(widgetId);
      }
    }, false);
  }

  /**
   * Submit form on successful callback
   *
   * @param token
   * @param form
   */
  onSproutFormsRecaptchaSuccess(token, form) {
    if (form) {
      form.submit();
    }
  }

  /**
   * Normalize 'inline' badge setting
   *
   * @param badge
   * @returns string
   */
  invisibleRecaptchaGetBadgePosition(badge) {
    let inlineBadges = ['inline-badge', 'inline-text'];

    if (inlineBadges.indexOf(badge) >= 0) {
      return 'inline';
    }

    return badge;
  }
}

window.SproutFormsGoogleRecaptchaInvisible = SproutFormsGoogleRecaptchaInvisible;