{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='contests' headerPayload=$headerPayload htmlTitle="{#omegaupTitleContest#}" inline}

{if $privateContestsAlert eq true}
	<div class="alert alert-info">
		<span class="message">
			{#messageMakeYourContestsPublic#}
		</span>
	</div>
{/if}

{include file='contest.list.tpl' inline}

{include file='footer.tpl' inline}
