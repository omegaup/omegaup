{include file='redirect.tpl'}
{include file='head.tpl' navbarSection='contests' htmlTitle="{#omegaupTitleContest#}"}

{if $privateContestsAlert eq true}
	<div class="alert alert-info">
		<span class="message">
			{#messageMakeYourContestsPublic#}
		</span>
	</div>
{/if}

{include file='contest.list.tpl'}

{include file='footer.tpl'}
