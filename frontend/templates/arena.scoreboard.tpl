{include file='head.tpl' jsfile=$jsfile inArena=true}
		<div>
			<div id="title">
				<h1>
					{if $isContest}
					<span class="contest-title"></span>
					{else}
					<span class="course-title"></span>
					{/if}
					<sup class="socket-status" title="WebSocket"></sup></h1>
				<div class="clock">00:00:00</div>
			</div>
			<div id="ranking">
				<div></div>
			</div>
		</div>
	</div>
</body>
</html>
