<!DOCTYPE html>
<html lang="{{ LOCALE }}" class="h-100">
  <head data-locale="{{ LOCALE }}">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {% if NEW_RELIC_SCRIPT %}
      {{ NEW_RELIC_SCRIPT|raw }}
    {% endif %}

    {% if GOOGLECLIENTID %}
      <meta name="google-signin-client_id" content="{{ GOOGLECLIENTID }}" />
    {% endif %}

    <script type="text/javascript" src="{% versionHash '/js/error_handler.js' %}"></script>
    <title>{{ title }} &ndash; omegaUp</title>
    <script type="text/javascript" src="{% versionHash '/third_party/js/jquery-3.5.1.min.js' %}"></script>
    <script type="text/javascript" src="{% versionHash '/js/jquery_error_handler.js' %}"></script>
    <script type="text/javascript" src="{% versionHash '/third_party/js/highstock.js' %}" defer></script>
    <script type="text/javascript" src="{% versionHash '/third_party/js/sugar.js' %}"></script>
    {% jsInclude 'omegaup' %}

    {% if jsfile %}
      <script type="text/javascript" src="{{ jsfile }}" defer></script>
    {% endif %}

    {% if scripts %}
      {% for script in scripts %}
        <script type="text/javascript" src="{{ script }}" defer async></script>
      {% endfor %}
    {% endif %}

    <script type="text/javascript" src="{% versionHash '/js/head.sugar_locale.js' %}" defer></script>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="/third_party/bootstrap-4.5.0/css/bootstrap.min.css"/>
    <script src="/third_party/bootstrap-4.5.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="{% versionHash '/css/dist/omegaup_styles.css' %}">
    <link rel="shortcut icon" href="/favicon.ico" />

    {% if ENABLED_EXPERIMENTS %}
        <script type="text/plain" id="omegaup-enabled-experiments">{{ ENABLED_EXPERIMENTS|join(',') }}</script>
    {% endif %}

    {% if recaptchaFile %}
        <script type="text/javascript" src="{{ recaptchaFile }}"></script>
    {% endif %}
  </head>

  <body class="d-flex flex-column h-100{% if OMEGAUP_LOCKDOWN %} lockdown{% endif %}">
    <script type="text/json" id="header-payload">{{ headerPayload|json_encode|raw }}</script>
    {% if not hideFooterAndHeader %}
      <div id="common-navbar"></div>
      {% jsInclude 'common_navbar' omitRuntime %}
    {% endif %}
    <main role="main" {% if not fullWidth %}class="container-lg p-5"{% endif %}>
      <div class="alert mt-0" id="status" style="display: none;">
        <button type="button" class="close" id="alert-close">&times;</button>
        <span class="message"></span>
      </div>
      {% if OMEGAUP_MAINTENANCE %}
        <div id="announcement" class="alert alert-info mt-0">
          {{ OMEGAUP_MAINTENANCE|raw }}
        </div>
      {% endif %}

      <script type="text/json" id="payload">{{ payload|json_encode|raw }}</script>
      {% entrypoint %}
      <div id="main-container"></div>
    </main>
    {% if OMEGAUP_GA_TRACK == 1 %}
      <script async src="https://www.googletagmanager.com/gtag/js?id=G-PBDCQK1GEQ"></script>
      <script type="text/javascript" src="{% versionHash '/js/analytics.js' %}"></script>
    {% endif %}
    {% if not headerPayload.inContest and not hideFooterAndHeader %}
    <div id="common-footer"></div>
    {% jsInclude 'common_footer' omitRuntime %}
    {% endif %}

  </body>
  <script type="text/javascript" src="{% versionHash '/js/status.dismiss.js' %}" defer></script>
</html>
