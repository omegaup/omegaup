{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='problems' headerPayload=$headerPayload htmlTitle="{#omegaupTitleProblemNew#}" inline}

<div id="problem-new"></div>
<script type="text/json" id="problem-new-payload">{$problemNewPayload|json_encode}</script>
{js_include entrypoint="problem_new"}
{include file='footer.tpl' inline}
