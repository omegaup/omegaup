{assign var="htmlTitle" value="{#passwordResetRequestTitle#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}
<div id="password-reset" class="container">
	<h1>{#passwordResetRequestTitle#}</h1>
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			{if isset($REQUEST_STATUS) }
				{if $REQUEST_STATUS == "success" }
					<div class="alert alert-success" role="alert">
					{#passwordResetRequestSuccess#}
					</div>
				{else}
					<div class="alert alert-danger" role="alert">
					{#passwordResetRequestFailure#}
					</div>
				{/if}
			{/if}
			<form method="POST" action="/forgot_password.php">
				<div class="form-group">
					<label for="email">{#profileEmail#}</label>
					<input type="text" id="email" name="email" class="form-control"/>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-primary form-control">{#passwordResetRequestSubmit#}</input>
				</div>
			</form>
		</div>
	</div>
</div>
{include file='footer.tpl'}
