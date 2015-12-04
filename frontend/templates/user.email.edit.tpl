{extends file="profile.tpl"} 
{block name="content"}
	<div class="col-md-10">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{#userEmailEditEmail#}</h2>
			</div>
			<div class="panel-body">
				<form id="user_edit_email_form" class="form-horizontal" role="form">
					
					<div class="form-group">
						<label for="email" class="col-md-3 control-label">{#userEmailEditEmail#}</label>
						<div class="col-md-7">
							<input id='email' name='email' value='{$CURRENT_USER_EMAIL}' type='text' size='30' class="form-control">
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-md-offset-3 col-md-7">
							<button type='submit' class="btn btn-primary">{#wordsSaveChanges#}</button>							
						</div>
					</div>
					
				</form>
				<div id="wait" style="display: none;">{#userEmailEditSaving#}<img src="/media/wait.gif" /></div>
			</div>
		</div>
	</div>
							
	<script type="text/javascript" src="/js/user.email.edit.js?ver=4e55cf"></script>
{/block}
