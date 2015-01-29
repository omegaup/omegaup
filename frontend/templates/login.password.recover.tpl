{assign var="htmlTitle" value="{#passwordResetRequestTitle#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}
<div id="password-reset" class="container">
	<h1>{#passwordResetRequestTitle#}</h1>
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<form id="forgot-password-form" method="POST" action="/login/password/recover/">
				<div class="form-group">
					<label for="email">{#profileEmail#}</label>
					<input type="text" id="email" name="email" class="form-control"/>
				</div>
				<div class="form-group">
					<button type="submit" id="submit" class="btn btn-primary form-control">{#wordsSend#}</input>
				</div>
			</form>
		</div>
	</div>
</div>
<script type='text/javascript' src='/js/reset.js' ></script>
{include file='footer.tpl'}
