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

    {# SEO Meta Tags #}
    {% if seoMeta.description %}
      <meta name="description" content="{{ seoMeta.description }}" />
    {% endif %}
    {% if seoMeta.keywords %}
      <meta name="keywords" content="{{ seoMeta.keywords }}" />
    {% endif %}
    {% if seoMeta.robots %}
      <meta name="robots" content="{{ seoMeta.robots }}" />
    {% else %}
      <meta name="robots" content="index, follow" />
    {% endif %}
    {% if seoMeta.author %}
      <meta name="author" content="{{ seoMeta.author }}" />
    {% endif %}

    {# Canonical URL #}
    {% if seoMeta.canonical %}
      <link rel="canonical" href="{{ seoMeta.canonical }}" />
    {% endif %}

    {# Open Graph / Facebook #}
    {% if seoMeta.og %}
      <meta property="og:type" content="{{ seoMeta.og.type|default('website') }}" />
      <meta property="og:title" content="{{ seoMeta.og.title|default(title ~ ' &ndash; omegaUp') }}" />
      {% if seoMeta.og.description %}
        <meta property="og:description" content="{{ seoMeta.og.description }}" />
      {% endif %}
      {% if seoMeta.og.url %}
        <meta property="og:url" content="{{ seoMeta.og.url }}" />
      {% endif %}
      {% if seoMeta.og.image %}
        <meta property="og:image" content="{{ seoMeta.og.image }}" />
        {% if seoMeta.og.imageWidth %}
          <meta property="og:image:width" content="{{ seoMeta.og.imageWidth }}" />
        {% endif %}
        {% if seoMeta.og.imageHeight %}
          <meta property="og:image:height" content="{{ seoMeta.og.imageHeight }}" />
        {% endif %}
      {% endif %}
      {% if seoMeta.og.siteName %}
        <meta property="og:site_name" content="{{ seoMeta.og.siteName }}" />
      {% else %}
        <meta property="og:site_name" content="omegaUp" />
      {% endif %}
      {% if seoMeta.og.locale %}
        <meta property="og:locale" content="{{ seoMeta.og.locale }}" />
      {% endif %}
    {% endif %}

    {# Twitter Card #}
    {% if seoMeta.twitter %}
      <meta name="twitter:card" content="{{ seoMeta.twitter.card|default('summary_large_image') }}" />
      <meta name="twitter:title" content="{{ seoMeta.twitter.title|default(title ~ ' &ndash; omegaUp') }}" />
      {% if seoMeta.twitter.description %}
        <meta name="twitter:description" content="{{ seoMeta.twitter.description }}" />
      {% endif %}
      {% if seoMeta.twitter.image %}
        <meta name="twitter:image" content="{{ seoMeta.twitter.image }}" />
      {% endif %}
      {% if seoMeta.twitter.site %}
        <meta name="twitter:site" content="{{ seoMeta.twitter.site }}" />
      {% endif %}
      {% if seoMeta.twitter.creator %}
        <meta name="twitter:creator" content="{{ seoMeta.twitter.creator }}" />
      {% endif %}
    {% endif %}

    {# hreflang tags for multi-language support #}
    {% if seoMeta.hreflang %}
      {% for lang, url in seoMeta.hreflang %}
        <link rel="alternate" hreflang="{{ lang }}" href="{{ url }}" />
      {% endfor %}
    {% endif %}

    {# Structured Data (JSON-LD) #}
    {% if seoMeta.structuredData %}
      {% if seoMeta.structuredData is iterable and seoMeta.structuredData|length > 0 %}
        {% for schema in seoMeta.structuredData %}
          <script type="application/ld+json">
            {{ schema|json_encode|raw }}
          </script>
        {% endfor %}
      {% else %}
        <script type="application/ld+json">
          {{ seoMeta.structuredData|json_encode|raw }}
        </script>
      {% endif %}
    {% endif %}
    <script type="text/javascript" src="{% versionHash '/third_party/js/jquery-3.5.1.min.js' %}"></script>
    <script type="text/javascript" src="{% versionHash '/js/jquery_error_handler.js' %}"></script>
    <script type="text/javascript" src="{% versionHash '/third_party/js/highstock.js' %}" defer></script>
    {% jsInclude 'omegaup' %}

    {% if jsfile %}
      <script type="text/javascript" src="{{ jsfile }}" defer></script>
    {% endif %}

    {% if scripts %}
      {% for script in scripts %}
        <script type="text/javascript" src="{{ script }}" defer async></script>
      {% endfor %}
    {% endif %}

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="/third_party/bootstrap-4.5.0/css/bootstrap.min.css"/>
    <script src="/third_party/bootstrap-4.5.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="{% versionHash '/css/dist/omegaup_styles.css' %}">
    
    {# Favicons #}
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="icon" type="image/png" sizes="76x76" href="/favicon-76x76.png" />
    {% if seoMeta.appleTouchIcon %}
      <link rel="apple-touch-icon" href="{{ seoMeta.appleTouchIcon }}" />
    {% else %}
      <link rel="apple-touch-icon" href="/favicon-76x76.png" />
    {% endif %}

    {% if ENABLED_EXPERIMENTS %}
        <script type="text/plain" id="omegaup-enabled-experiments">{{ ENABLED_EXPERIMENTS|join(',') }}</script>
    {% endif %}

    {% if recaptchaFile %}
        <script type="text/javascript" src="{{ recaptchaFile }}"></script>
    {% endif %}
  </head>

  <body class="d-flex flex-column h-100{% if OMEGAUP_LOCKDOWN %} lockdown{% endif %}">
    <script type="text/json" id="header-payload">{{ headerPayload|json_encode|raw }}</script>
    {% jsInclude 'common_navbar' omitRuntime %}
    {% if not hideFooterAndHeader %}
      <div id="common-navbar"></div>
    {% endif %}
    <main role="main" {% if not fullWidth %}class="container-lg py-5 px-3 px-md-5"{% endif %}>
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
    {% jsInclude 'common_scroll_to_top' omitRuntime %}
      <div id="scroll-to-top"></div>
    {% jsInclude 'common_footer' omitRuntime %}
    {% if not headerPayload.inContest and not hideFooterAndHeader %}
      <div id="common-footer"></div>
    {% endif %}

  </body>
  <script type="text/javascript" src="{% versionHash '/js/status.dismiss.js' %}" defer></script>
</html>
