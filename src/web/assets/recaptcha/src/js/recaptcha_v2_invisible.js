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
      let form = recaptchaContainer.closest('form');
      this.addFormEventListener(form);

      let widgetId = self.grecaptcha.render(recaptchaContainer.id, {
        'sitekey': self.siteKey,
        'theme': self.theme,
        'badge': self.badgePosition,
        'size': 'invisible',
        'callback': () => {
          let event = self.sproutFormsSubmitEvent;
          event.detail.submitHandler.handleFormSubmit();
          self.grecaptcha.reset();
        },
        'expired-callback': () => {
          let event = self.sproutFormsSubmitEvent;
          event.detail.submitHandler.onFormSubmitCancelledEvent();
          self.grecaptcha.reset();
        }
      });

      form.setAttribute('data-google-recaptcha-widget-id', widgetId);

      let submitButton = form.querySelector('[type="submit"]');
      let recaptchaInlineTextTerms = form.querySelector('.google-recaptcha-inline-text-terms');

      // Place the reCAPTCHA terms after the submit button
      if (this.badge === 'inline-text') {
        submitButton.parentNode.insertBefore(recaptchaInlineTextTerms, submitButton.nextSibling);
        recaptchaInlineTextTerms.style.display = 'block';
      } else {
        submitButton.parentNode.insertBefore(recaptchaContainer, submitButton.nextSibling);
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

    form.addEventListener('onSproutFormsSubmit', function(event) {

      let targetForm = event.target;
      let widgetId = targetForm.getAttribute('data-google-recaptcha-widget-id');

      // Add the sproutFormsSubmit Event to access later from the grecaptcha callbacks
      self.sproutFormsSubmitEvent = event;

      // Make sure to run reCAPTCHA before submitting form
      if (!self.grecaptcha.getResponse(widgetId)) {
        // Cancel the default Sprout Forms submissions. We'll handle it from here
        // because we need to wait for our callbacks.
        event.preventDefault();
        self.grecaptcha.execute(widgetId);
      }

    }, false);
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