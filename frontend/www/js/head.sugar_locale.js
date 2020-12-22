(function () {
  var locale = $('head').attr('data-locale');
  if (locale == 'pseudo') {
    locale = 'en';
  }
  Date.setLocale(locale);
})();
