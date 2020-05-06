{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='contests' headerPayload=$headerPayload htmlTitle="{#omegaupTitleContestNew#}" inline}
<script type="text/javascript" src="/third_party/js/bootstrap-select.min.js"></script>
<link rel="stylesheet" href="/third_party/css/bootstrap-select.min.css">

<div id="contest-new"></div>
<script type="text/json" id="contest-new-payload">{$contestNewPayload|json_encode}</script>
{js_include entrypoint="contest_new"}

{include file='footer.tpl' inline}
