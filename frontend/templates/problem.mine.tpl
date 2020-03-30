{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='problems' headerPayload=$headerPayload htmlTitle="{#omegaupTitleMyProblemsList#}" inline}
<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="problem-mine"></div>
{js_include entrypoint="problem_mine"}
{include file='footer.tpl' inline}
