{include file='head.tpl' navbarSection='problems' htmlTitle="{#omegaupTitleProblems#}" inline}

<div id="parent_problems_list">
	{include file='problem_search_bar.tpl' inline}
	<script type="text/json" id="payload">{['problems' => $problems, 'logged_in' => $LOGGED_IN == "1", 'current_tags' => $current_tags]|json_encode}</script>
	<div id="problem-list"></div>
	<script type="text/javascript" src="{version_hash src="/js/dist/problem_list.js"}"></script>
	{include file='pager_bar.tpl' inline}
</div>

{include file='footer.tpl' inline}
