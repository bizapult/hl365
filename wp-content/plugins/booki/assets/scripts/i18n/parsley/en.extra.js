window.ParsleyConfig = window.ParsleyConfig || {};
window.ParsleyConfig.i18n = window.ParsleyConfig.i18n || {};

window.ParsleyConfig.i18n.en = jQuery.extend(window.ParsleyConfig.i18n.en || {}, {
  dateiso:  "This value should be a valid date (YYYY-MM-DD).",
  minwords: "This value is too short. It should have %s words or more.",
  maxwords: "This value is too long. It should have %s words or fewer.",
  words:    "This value length is invalid. It should be between %s and %s words long."
});
