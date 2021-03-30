<!DOCTYPE html>
<html lang="{#locale#}" class="h-100">
  <head data-locale="{#locale#}">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    {if !is_null($smarty.const.NEW_RELIC_SCRIPT)}
      {$smarty.const.NEW_RELIC_SCRIPT}
    {/if}

    {if isset($inArena) && $inArena}
      {assign var='navbarSection' value='arena'}
    {elseif isset($GOOGLECLIENTID) && !empty($GOOGLECLIENTID)}
      <meta name="google-signin-client_id" content="{$GOOGLECLIENTID}" />
    {/if}

    <script type="text/javascript" src="{version_hash src="/js/error_handler.js"}"></script>
    <title>{$title} &ndash; omegaUp</title>
    <script type="text/javascript" src="{version_hash src="/third_party/js/jquery-3.5.1.min.js"}"></script>
    <script type="text/javascript" src="{version_hash src="/js/jquery_error_handler.js"}"></script>
    <script type="text/javascript" src="{version_hash src="/third_party/js/highstock.js" defer}" defer></script>
    <script type="text/javascript" src="{version_hash src="/third_party/js/sugar.js" defer}"></script>
    {js_include entrypoint="omegaup" runtime}

    {if isset($inArena) && $inArena}
      {js_include entrypoint="arena"}
    {/if}

    {if isset($jsfile)}
      <script type="text/javascript" src="{$jsfile}" defer></script>
    {/if}

    {if isset($scripts)}
      {foreach from=$scripts item=$script}
        <script type="text/javascript" src="{$script}" defer async></script>
      {/foreach}
    {/if}

    <script type="text/javascript" src="{version_hash src="/js/head.sugar_locale.js"}" defer></script>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="/third_party/bootstrap-4.5.0/css/bootstrap.min.css"/>
    <script src="/third_party/bootstrap-4.5.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="{version_hash src="/css/dist/omegaup_styles.css"}">
    <link rel="shortcut icon" href="/favicon.ico" />

    {if isset($inArena) && $inArena}
      <link rel="stylesheet" type="text/css" href="{version_hash src="/css/arena.css"}" />
    {/if}

    {if !empty($ENABLED_EXPERIMENTS)}
        <script type="text/plain" id="omegaup-enabled-experiments">{','|implode:$ENABLED_EXPERIMENTS}</script>
    {/if}

    {if isset($recaptchaFile)}
        <script type="text/javascript" src="{$recaptchaFile}"></script>
    {/if}
  </head>

  <body class="d-flex flex-column h-100">
    <script type="text/json" id="header-payload">{$headerPayload|json_encode}</script>
    <div id="common-navbar"></div>
    {js_include entrypoint="common_navbar_v2"}
	  <main role="main" {if (!isset($fullWidth) || !$fullWidth)}class="container-lg p-5"{/if}>
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
    {if $headerPayload.inContest eq false}
    <div id="common-footer"></div>
    {js_include entrypoint="common_footer_v2"}
    {/if}

  </body>
  <link id="dark-theme-style" rel="stylesheet" />
  <script type="text/javascript" src="{version_hash src="/js/status.dismiss.js"}" defer></script>
</html>
