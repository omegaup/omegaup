{include file='head.tpl' navbarSection='arena' headerPayload=$headerPayload inline}
<div id="arena-contest-list"></div>
<script type="text/json" id="payload">{$payload|json_encode}</script>
{js_include entrypoint="arena_contest_list"}
{include file='common.analytics.tpl' inline}
	</body>
</html>
