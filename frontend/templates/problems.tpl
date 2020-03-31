{include file='head.tpl' navbarSection='problems' headerPayload=$headerPayload htmlTitle="{#omegaupTitleProblems#}" inline}

<div id="parent_problems_list">
	<script type="text/json" id="payload">{$payload|json_encode}</script>
	<div id="problem-list"></div>
	{js_include entrypoint="problem_list"}
</div>

{include file='footer.tpl' inline}
