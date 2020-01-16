// Prepare reCAPTCHA for all Sprout Forms on page
class SproutFormsGoogleRecaptcha {

  constructor(settings) {
    this.siteKey = settings.siteKey;
    this.badgePosition = settings.badgePosition;
    this.grecaptcha = settings.grecaptcha;

    this.setupRecaptchaInstances();
  }

  setupRecaptchaInstances() {
    let self = this;
    const sproutFormsRecaptchaContainers = document.querySelectorAll('.google-recaptcha-container');

    for (const recaptchaContainer of sproutFormsRecaptchaContainers) {
      this.grecaptcha.render(recaptchaContainer.id, {
        'sitekey': self.siteKey,
        'badge': self.badgePosition,
        'size': 'invisible',
        'callback': self.onSproutFormsRecaptchaSuccess
      });

      let form = recaptchaContainer.parentElement;

      this.addFormEventListener(form);
    }
  }

  addFormEventListener(form) {
    let self = this;
    // Add Form Event Listeners
    form.addEventListener('submit', function (event) {
      event.preventDefault();

      // Disable the tag we use to identify the form submitting
      form.setAttribute('data-google-recaptcha-processing', false);

      let targetForm = event.target;
      let recaptchaContainerId = targetForm.id + '-google-recaptcha';
      let recaptchaContainer = document.getElementById(recaptchaContainerId);
      let recaptchaWidgetTextarea = recaptchaContainer.querySelector('textarea');
      let widgetIdString = recaptchaWidgetTextarea.id;

      // Determine the reCAPTCHA Widget ID from the element ID
      let matches = widgetIdString.match(/\d+$/);
      let widgetId = matches ? matches[0] : 0;

      // Make sure to run reCAPTCHA before submitting form
      if (!self.grecaptcha.getResponse(widgetId)) {
        event.preventDefault();

        // Set the processing status to true so we can check for this in our success callback
        form.setAttribute('data-google-recaptcha-processing', true);

        // Run reCAPTCHA
        self.grecaptcha.execute(widgetId);
      }
    }, false);
  }

  // Submit form on successful callback
  onSproutFormsRecaptchaSuccess(token) {
    // Look for the data attribute that indicates the form is being validated by reCAPTCHA
    let form = document.querySelector('[data-google-recaptcha-processing]');
    let process = form.getAttribute('data-google-recaptcha-processing');

    // If process is true we have a successful callback and have identified the form being submitted
    if (process) {
      form.submit();
    }
  }
}

window.SproutFormsGoogleRecaptcha = SproutFormsGoogleRecaptcha;