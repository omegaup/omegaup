{include file='head.tpl' htmlTitle='{#passwordResetResetTitle#}'}
<div id="password-reset" class="container">
	<h1>{#passwordResetResetTitle#}</h1>
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<form id='reset-password-form' method="POST" action="/login/password/reset/">
				<input type="hidden" id="email" name="email" value="{$EMAIL|escape:'html'}" />
				<input type="hidden" id="reset_token" name="reset_token" value="{$RESET_TOKEN|escape:'html'}" />
				<div class="form-group">
					<label for="password">{#passwordResetPassword#}</label>
					<input type="password" id="password" name="password" class="form-control"/>
				</div>
				<div class="form-group">
					<label for="password_confirmation">{#passwordResetPasswordConfirmation#}</label>
					<input type="password" id="password_confirmation" name="password_confirmation" class="form-control"/>
				</div>
				<div class="form-group">
					<button type="submit" id="submit" class="btn btn-primary form-control">{#wordsSaveChanges#}</input>
				</div>
			</form>
		</div>
	</div>
</div>
<script type='text/javascript' src="{version_hash src="/js/reset.js"}" ></script>
{include file='footer.tpl'}
