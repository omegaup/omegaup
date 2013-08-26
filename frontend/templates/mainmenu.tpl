	<div id="content">
		{if $ERROR_TO_USER eq 'USER_OR_PASSWORD_WRONG'} 
			<div class="alert alert-danger">
				Your credentials are wrong
			</div>
		{/if} 
		{if $ERROR_TO_USER eq 'EMAIL_NOT_VERIFIED'} 
			<div class="alert alert-danger">
				Your email is not verified yet. Please check your e-mail.
			</div>
		{/if} 
