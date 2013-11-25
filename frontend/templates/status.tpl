

{if $LOGGED_IN eq 1 and $CURRENT_USER_IS_EMAIL_VERIFIED eq 0}
	<div class="alert alert-danger" id='email-verification-alert'>
		<button type="button" class="close" id="email-verification-alert-close">&times;</button>
		<span class="message">
			No podrás loggearte con este usuario si no verificas tu correo. 
			Actualiza tu email <b><a href="/useremailedit.php">aquí</a></b>
			y responde el correo de verificación. Si tienes dudas contáctanos en joe@omegaup.com .
		</span>
	</div>
{/if}

{if $LOGGED_IN eq 1 and $CURRENT_USER_PRIVATE_CONTESTS_COUNT gt 0}
	<div class="alert alert-info" id='private-contests-count-alert'>
		<button type="button" class="close" id="private-contests-count-alert-close">&times;</button>
		<span class="message">			
			Tienes <b>{$CURRENT_USER_PRIVATE_CONTESTS_COUNT} concurso{if $CURRENT_USER_PRIVATE_CONTESTS_COUNT gt 1}s{/if} 
			privado{if $CURRENT_USER_PRIVATE_CONTESTS_COUNT gt 1}s{/if}</b> registrado{if $CURRENT_USER_PRIVATE_CONTESTS_COUNT gt 1}s{/if} 
			en omegaUp.						
			Por favor revisa <a href="/contests.php">aquí</a> si alguno de tus concursos ya puede ser <b>público</b> para ayudar a la comunidad.
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
</script>
