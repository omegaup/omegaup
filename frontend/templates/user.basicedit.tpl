{extends file="profile.tpl"}
{block name="content"}
	<div class="col-md-10">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{#userEditEditProfile#}</h2>
			</div>
			<div class="panel-body">
				<form id="user_profile_form" class="form-horizontal" role="form">
					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#profileUsername#}</label>
						<div class="col-md-7">
							<input id='username' name='username' value='' type='text' size='30' class="form-control">
						</div>
					</div>

					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#profile#}</label>
						<div class="col-md-7">
							<input id='name' name='name' value='' type='text' size='30' class="form-control">
						</div>
					</div>

					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#userEditChangePasswordNewPassword#}</label>
						<div class="col-md-7">
							<input id='new-password-1' name='name' value='' type='password' size='30' class="form-control">
						</div>
					</div>

					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#userEditChangePasswordRepeatNewPassword#}</label>
						<div class="col-md-7">
							<input id='new-password-2' name='name' value='' type='password' size='30' class="form-control">
						</div>
					</div>

					<div class="form-group">
						<div class="col-md-offset-3 col-md-7">
							<button type='submit' class="btn btn-primary">{#wordsSaveChanges#}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script type="text/javascript" src="{version_hash src="/js/user.basicedit.js"}"></script>
{/block}
