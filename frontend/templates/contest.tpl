{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleContest#}"}
{include file='head.tpl' navbarSection="contests"}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

{if $LOGGED_IN eq 1 and $CURRENT_USER_PRIVATE_CONTESTS_COUNT gt 0 and $PRIVATE_CONTESTS_ALERT eq 1}
	<div class="alert alert-info">
		<span class="message">
			{#messageMakeYourContestsPublic#}
		</span>
	</div>
{/if}

<div class="panel panel-default">
	<div class="panel-body">
		<div class="bottom-margin">
			<a href="/contest/new/" class="btn btn-primary" id="contest-create">{#contestsCreateNew#}</a>
			<a href="/scoreboardmerge.php" class="btn btn-default" id="scoreboard-merge">{#contestsJoinScoreboards#}</a>
		</div>

		<div id="parent_contest_list">
			{include file='contest.list.tpl'}
		</div>
	</div>
</div>

{include file='footer.tpl'}
