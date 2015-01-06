{assign var="htmlTitle" value="{#passwordResetResetTitle#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}
<div id="password-reset" class="container">
	<h1>{#passwordResetResetTitle#}</h1>
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			{if isset($REQUEST_STATUS) }
				{if $REQUEST_STATUS == "success" }
					<div class="alert alert-success" role="alert">
					{#passwordResetResetSuccess#}
					</div>
				{elseif $REQUEST_STATUS == "failure"}
					<div class="alert alert-danger" role="alert">
					{#passwordResetResetFailure#}
					</div>
				{elseif $REQUEST_STATUS == "expired"}
					<div class="alert alert-danger" role="alert">
					{#passwordResetResetExpired#}
					</div>
				{/if}
			{/if}
			<form method="POST" action="/reset_password.php">
				<input type="hidden" name="email" value="{$EMAIL}" />
				<input type="hidden" name="reset_token" value="{$RESET_TOKEN}" />
				<div class="form-group">
					<label for="password">{#passwordResetPassword#}</label>
					<input type="password" id="password" name="password" class="form-control"/>
				</div>
				<div class="form-group">
					<label for="password_confirmation">{#passwordResetPasswordConfirmation#}</label>
					<input type="password" id="password_confirmation" name="password_confirmation" class="form-control"/>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-primary form-control">{#passwordResetResetSave#}</input>
				</div>
			</form>
		</div>
	</div>
</div>
{include file='footer.tpl'}
