/**
 * Prepare reCAPTCHA for ALL Sprout Forms on page
 */
class SproutFormsGoogleRecaptchaCheckbox {

  constructor(settings) {
    this.siteKey = settings.siteKey;
    this.theme = settings.theme ?? null;
    this.size = settings.size ?? null;
    this.customValidityText = settings.customValidityText;
    this.grecaptcha = settings.grecaptcha;

    this.initRecaptchas();

    let self = this;
    window.onload = function() {
      self.makeRecaptchasRequired();
    }
  }

  initRecaptchas() {
    let self = this;
    let sproutFormsRecaptchaContainers = document.querySelectorAll('.google-recaptcha-container');

    for (let recaptchaContainer of sproutFormsRecaptchaContainers) {
      let form = recaptchaContainer.closest('form');
      this.addFormEventListener(form);

      let widgetId = this.grecaptcha.render(recaptchaContainer.id, {
        'sitekey': self.siteKey,
        'theme': self.theme,
        'size': self.size,
        'callback': function() {
          self.onSproutFormsRecaptchaSuccess(form);
        },
        'expired-callback': function() {
          self.onSproutFormsRecaptchaExpired(form);
          self.grecaptcha.reset();
        }
      });

      form.setAttribute('data-google-recaptcha-widget-id', widgetId);
    }
  }

  /**
   * Make reCAPTCHA not required any longer if successful callback
   *
   * @param form
   */
  onSproutFormsRecaptchaSuccess(form) {
    let recaptchaResponseTextarea = form.querySelector('.g-recaptcha-response');
    recaptchaResponseTextarea.required = false;
    recaptchaResponseTextarea.setCustomValidity('');
  }

  /**
   * Make reCAPTCHA required again after expiration resets things
   *
   * @param form
   */
  onSproutFormsRecaptchaExpired(form) {
    let self = this;
    // Google swaps in new recaptcha textarea after this triggers
    // Add delay to target the new textarea over the old
    setTimeout(function() {
      let recaptchaResponseTextarea = form.querySelector('.g-recaptcha-response');
      self.makeRecaptchaRequired(recaptchaResponseTextarea);
    }, 500);
  }

  /**
   * Make all reCAPTCHAs required after initial page load
   */
  makeRecaptchasRequired() {
    let self = this;

    let recaptchaResponseTextareas = document.querySelectorAll('.g-recaptcha-response');
    for (const recaptchaResponseTextarea of recaptchaResponseTextareas) {
      self.makeRecaptchaRequired(recaptchaResponseTextarea);
    }
  }

  /**
   * Make a single reCAPTCHA textarea required
   *
   * @param recaptchaResponseTextarea
   */
  makeRecaptchaRequired(recaptchaResponseTextarea) {
    if (recaptchaResponseTextarea) {
      recaptchaResponseTextarea.required = true;
      recaptchaResponseTextarea.setCustomValidity(this.customValidityText);
    }
  }

  /**
   * Adds Event Listener to ensure we reset reCAPTCHA after AJAX submissions
   *
   * @param form
   */
  addFormEventListener(form) {
    let self = this;

    form.addEventListener('afterSproutFormsSubmit', function(event) {
      console.log('sss');
      let targetForm = event.target;
      let widgetId = targetForm.getAttribute('data-google-recaptcha-widget-id');
      self.grecaptcha.reset(widgetId);
      self.makeRecaptchasRequired();

    }, false);
  }
}

window.SproutFormsGoogleRecaptchaCheckbox = SproutFormsGoogleRecaptchaCheckbox;