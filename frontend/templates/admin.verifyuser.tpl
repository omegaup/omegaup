{include file='redirect.tpl'}
{include file='head.tpl'}

<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">Force user validation</h2>
	</div>
	<div class="panel-body">
		<form class="form bottom-margin" id="verify-user-form">
			<div class="form-group">
				<label for="username">Username</label>
				<input id='username' name='username' value='' type='text' size='20' class="form-control" />
			</div>

			<button class="btn btn-primary" type='submit'>Verify user</button>
		</form>
	</div>
</div>

<script type="text/javascript" src="{version_hash src="/js/admin.verifyuser.js"}"></script>
