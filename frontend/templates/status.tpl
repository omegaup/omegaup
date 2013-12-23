

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
			Por favor revisa <a href="/myproblems.php">aquí</a> si alguno de tus problemas ya puede ser <b>público</b> para ayudar a la comunidad =).
		</span>
	</div>
{/if}


{if isset($STATUS_ERROR) and $STATUS_ERROR neq ''} 
	<div class="alert alert-danger" id='status'>
		<button type="button" class="close" id="alert-close">&times;</button>
		<span class="message">{$STATUS_ERROR}</span>
	</div>
{else if isset($STATUS_SUCCESS) and $STATUS_SUCCESS neq ''}
	<div class="alert alert-success" id='status'>
		<button type="button" class="close" id="alert-close">&times;</button>
		<span class="message">{$STATUS_SUCCESS}</span>
	</div>
{else}
	<div class="alert" id='status' style="display: none;">
		<button type="button" class="close" id="alert-close">&times;</button>
		<span class="message"></span>
	</div>
{/if}

<script type="text/javascript">
	$("#alert-close").click(function () {
		$("#status").slideUp();
	});
	
	$("#email-verification-alert-close").click(function () {
		$("#email-verification-alert").slideUp();
	});
	
	$("#private-contests-count-alert-close").click(function () {
		$("#private-contests-count-alert").slideUp();
	});
	
	$("#private-problems-count-alert-close").click(function () {
		$("#private-problems-count-alert").slideUp();
	});
</script>
