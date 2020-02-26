{include file='redirect.tpl' inline}
{include file='head.tpl' htmlTitle="{#omegaupTitleContestStats#}" inline}

<div id="contest-stats"></div>
<script type="text/json" id="payload">{$payload|json_encode}</script>
{js_include entrypoint="contest_stats"}

{include file='footer.tpl' inline}
