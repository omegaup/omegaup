{include file='head.tpl' inArena=true inline}
<script type="text/json" id="header-payload">{$headerPayload|json_encode}</script>
{if $titleClassName == 'course-title'}
        {js_include entrypoint="course_scoreboard"}
{/if}
		<div>
			<div id="title">
				<h1>
					<span class="{$titleClassName}"></span>
					<sup class="socket-status" title="WebSocket"></sup></h1>
				<div class="clock">00:00:00</div>
			</div>
			<div id="ranking">
				<div></div>
			</div>
		</div>
	</div>
{include file='common.analytics.tpl' inline}
</body>
</html>
