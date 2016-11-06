{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleContest#}"}
{include file='head.tpl' navbarSection="contests"}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

{if $PRIVATE_CONTESTS_ALERT eq 1}
	<div class="alert alert-info">
		<span class="message">
			{#messageMakeYourContestsPublic#}
		</span>
	</div>
{/if}

{include file='contest.list.tpl'}

{include file='footer.tpl'}
