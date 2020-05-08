<!DOCTYPE html>
<html lang="{#locale#}" class="h-100">
  {include file='head_v2.tpl' htmlTitle="{$title|escape}" inline}
  <body class="d-flex flex-column h-100">
    {include file='navbar_v2.tpl' headerPayload=$headerPayload inline}
	  <main role="main">
      {if (!isset($inArena) || !$inArena) && isset($ERROR_MESSAGE)}
        <div class="alert alert-danger">
          {$ERROR_MESSAGE}
        </div>
      {/if}
      {include file='status.tpl' inline}

      <script type="text/json" id="payload">{$payload|json_encode}</script>
      {block name="entrypoint"}{/block}
      <div {if (!isset($fullWidth) || !$fullWidth)}class="container-xl pt-4 mb-4"{/if}>
        <div id="main-container"></div>
      </div>
    </main>
    {include file='footer_v2.tpl' inline}
  </body>
</html>
