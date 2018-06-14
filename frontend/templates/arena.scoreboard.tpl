{include file='head.tpl' inArena=true}
        <script type="text/javascript" src="{version_hash src="/js/dist/course_scoreboard.js"}"></script>
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
{include file='common.analytics.tpl'}
</body>
</html>
