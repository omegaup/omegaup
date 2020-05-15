<!DOCTYPE html>
<html lang="{#locale#}" class="h-100">
  <head data-locale="{#locale#}">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    {if !is_null($smarty.const.NEW_RELIC_SCRIPT)}
      {$smarty.const.NEW_RELIC_SCRIPT}
    {/if}

    {if isset($inArena) && $inArena}
      {assign var='LOAD_MATHJAX' value='true'}
      {assign var='navbarSection' value='arena'}
    {else}
      <meta name="google-signin-client_id" content="{$GOOGLECLIENTID}" />
    {/if}

    <script type="text/javascript" src="{version_hash src="/js/error_handler.js"}"></script>
    <title>{if isset($title)}{$title} &ndash; {/if}omegaUp</title>
    <script type="text/javascript" src="{version_hash src="/third_party/js/jquery-3.5.1.min.js"}"></script>
    <script type="text/javascript" src="{version_hash src="/js/jquery_error_handler.js"}"></script>
    <script type="text/javascript" src="{version_hash src="/third_party/js/highstock.js" defer}" defer></script>
    <script type="text/javascript" src="{version_hash src="/third_party/js/sugar.js" defer}"></script>
    {js_include entrypoint="omegaup" runtime}
    <script type="text/javascript" src="{version_hash src="/js/require_helper.js"}"></script>

    {if isset($inArena) && $inArena}
      {js_include entrypoint="arena"}
    {/if}

    {if (isset($inArena) && $inArena) || (isset($loadMarkdown) && $loadMarkdown)}
      <script type="text/javascript" src="{version_hash src="/third_party/js/jquery.tableSort.js"}" defer></script>
    {/if}

    {if isset($jsfile)}
      <script type="text/javascript" src="{$jsfile}" defer></script>
    {/if}

    {if isset($LOAD_MATHJAX) && $LOAD_MATHJAX}
      <script type="text/javascript" src="{version_hash src="/js/mathjax-config.js"}" defer></script>
      <script type="text/javascript" src="/third_party/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML" defer></script>
    {/if}

    <script type="text/javascript" src="{version_hash src="/js/langtools.js"}" defer></script>
    <script type="text/javascript" src="{version_hash src="/js/head.sugar_locale.js"}" defer></script>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="/third_party/bootstrap-4.4.1/css/bootstrap.min.css"/>
    <script src="/third_party/bootstrap-4.4.1/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="{version_hash src="/css/dist/omegaup_styles.css"}">
    <link rel="shortcut icon" href="/favicon.ico" />

    {if isset($inArena) && $inArena}
      <link rel="stylesheet" type="text/css" href="{version_hash src="/ux/arena.css"}" />
    {/if}

    {if !empty($ENABLED_EXPERIMENTS)}
        <script type="text/plain" id="omegaup-enabled-experiments">{','|implode:$ENABLED_EXPERIMENTS}</script>
    {/if}

    {if isset($recaptchaFile)}
        <script type="text/javascript" src="{$recaptchaFile}"></script>
    {/if}
  </head>

  <body class="d-flex flex-column h-100">
    {include file='navbar_v2.tpl' headerPayload=$headerPayload inline}
	  <main role="main" {if (!isset($fullWidth) || !$fullWidth)}class="container-lg pt-4 mb-4"{/if}>
      {if (!isset($inArena) || !$inArena) && isset($ERROR_MESSAGE)}
        <div class="alert alert-danger">
          {$ERROR_MESSAGE}
        </div>
      {/if}
      {if isset($STATUS_ERROR) && $STATUS_ERROR !== ''}
        <div class="alert alert-danger mt-0" id="status">
          <button type="button" class="close" id="alert-close">&times;</button>
          <span class="message">{$STATUS_ERROR}</span>
        </div>
      {else if isset($STATUS_SUCCESS) && $STATUS_SUCCESS !== ''}
        <div class="alert alert-success mt-0" id="status">
          <button type="button" class="close" id="alert-close">&times;</button>
          <span class="message">{$STATUS_SUCCESS}</span>
        </div>
      {else}
        <div class="alert mt-0" id="status" style="display: none;">
          <button type="button" class="close" id="alert-close">&times;</button>
          <span class="message"></span>
        </div>
      {/if}
      {if $smarty.const.OMEGAUP_MAINTENANCE}
        <div id="announcement" class="alert alert-info mt-0">
          {$smarty.const.OMEGAUP_MAINTENANCE}
        </div>
      {/if}

      <script type="text/json" id="payload">{$payload|json_encode}</script>
      {block name="entrypoint"}{/block}
      <div id="main-container"></div>
    </main>
    {include file='common.analytics.tpl' inline}
    <div id="common-footer"></div>
    {js_include entrypoint="common_footer_v2"}

  </body>
  <script type="text/javascript" src="{version_hash src="/js/status.dismiss.js"}" defer></script>
</html>
