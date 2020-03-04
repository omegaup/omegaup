{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='problems' headerPayload=$headerPayload htmlTitle="{#omegaupTitleProblemStats#}" inline}

<div id="common-stats"></div>
<script type="text/json" id="payload">{$payload|json_encode}</script>
{js_include entrypoint="common_stats"}

{include file='footer.tpl' inline}
