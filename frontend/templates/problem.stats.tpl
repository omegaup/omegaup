{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='problems' headerPayload=$headerPayload htmlTitle="{#omegaupTitleProblemStats#}" inline}

<div id="problem-stats"></div>
<script type="text/json" id="payload">{$payload|json_encode}</script>
{js_include entrypoint="problem_stats"}

{include file='footer.tpl' inline}
