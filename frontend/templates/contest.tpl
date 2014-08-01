{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleContest#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

{if $LOGGED_IN eq 1 and $CURRENT_USER_PRIVATE_CONTESTS_COUNT gt 0}
	<div class="alert alert-info" id='private-contests-count-alert'>
		<button type="button" class="close" id="private-contests-count-alert-close">&times;</button>
		<span class="message">			
			Tienes <b>{$CURRENT_USER_PRIVATE_CONTESTS_COUNT} concurso{if $CURRENT_USER_PRIVATE_CONTESTS_COUNT gt 1}s{/if} 
			privado{if $CURRENT_USER_PRIVATE_CONTESTS_COUNT gt 1}s{/if}</b> registrado{if $CURRENT_USER_PRIVATE_CONTESTS_COUNT gt 1}s{/if} 
			en omegaUp.						
			Por favor revisa <a href="/contests.php">aquí</a> si alguno de tus concursos ya puede ser <b>público</b> para ayudar a la comunidad =).
		</span>
	</div>
{/if}

{if $LOGGED_IN eq 1 and $CURRENT_USER_PRIVATE_PROBLEMS_COUNT gt 0}
	<div class="alert alert-info" id='private-problems-count-alert'>
		<button type="button" class="close" id="private-problems-count-alert-close">&times;</button>
		<span class="message">			
			Tienes <b>{$CURRENT_USER_PRIVATE_PROBLEMS_COUNT} problema{if $CURRENT_USER_PRIVATE_PROBLEMS_COUNT gt 1}s{/if} 
			privado{if $CURRENT_USER_PRIVATE_PROBLEMS_COUNT gt 1}s{/if}</b> registrado{if $CURRENT_USER_PRIVATE_PROBLEMS_COUNT gt 1}s{/if} 
			en omegaUp.						
			Por favor revisa <a href="/problems/mine/">aquí</a> si alguno de tus problemas ya puede ser <b>público</b> para ayudar a la comunidad =).
		</span>
	</div>
{/if}

<div class="panel panel-default">
	<div class="panel-body">
		<div class="bottom-margin">
			<a href="/contestcreate.php" class="btn btn-primary" id="contest-create">{#contestsCreateNew#}</a>
			<a href="/scoreboardmerge.php" class="btn btn-default" id="scoreboard-merge">{#contestsJoinScoreboards#}</a>
		</div>

		<div id="parent_contest_list">
			{include file='contest.list.tpl'}
		</div>
	</div>
</div>

<script>
	$(".navbar #nav-contests").addClass("active");
</script>
	
{include file='footer.tpl'}
