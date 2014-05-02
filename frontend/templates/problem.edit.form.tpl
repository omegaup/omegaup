{if !isset($IS_UPDATE)}
	{assign "IS_UPDATE" 0}
{/if}

<form method='POST' action='{$smarty.server.REQUEST_URI}' id='problem_form' class="form" enctype="multipart/form-data">
	{if $IS_UPDATE eq 1}
		<div class="row">
			<div class="form-group col-md-6">
				<label for="problem_alias">{#wordsProblem#}</label>
				<select class="edit-problem-list" name='edit-problem-list' id='problem_alias' class="form-control">
						<option></option>
				</select>
			</div>
		</div>
	{/if}
	
	<div class="row">
		<div class="form-group col-md-6">
			<label for="problem_contents">{#problemEditFormFile#}</label>
			<input name="problem_contents" id="problem_contents" type="file" class="form-control" />
		</div>
		
		<div class="form-group  col-md-6">
			<label for="title">{#wordsTitle#}</label>
			<input id='title' name='title' value='{if $IS_UPDATE eq 0}{$TITLE|htmlspecialchars}{/if}' type='text' class="form-control" />
		</div>
	</div>
	
	<div class="row">
		<div class="form-group  col-md-6">
			<label for="alias">{#wordsAlias#}</label>
			<input id='alias' name='alias' value='{if $IS_UPDATE eq 0}{$ALIAS|htmlspecialchars}{/if}' type='text' class="form-control"{if $IS_UPDATE eq 1} disabled="disabled"{/if}/>
		</div>

		<div class="form-group col-md-6">
			<label for="validator">{#problemEditFormValidatorType#}</label>
			<select name='validator' id='validator' class="form-control" >
					<option value="token-caseless">{#problemEditFormTokenByToken#}, ignorando diferencias en mayúsculas/minúsculas (default)	</option>
					<option value="token-numeric">Tokens numéricos con tolerancia</option>		
					<option value="token">{#problemEditFormTokenByToken#}</option>		
					<option value="literal">Sólo salida, comparación literal</option>
					<option value="custom">Validador personalizado (validator.$lang$)</option>
			</select>
		</div>
	</div>
	
	<div class="row">
		<div class="form-group  col-md-6">
			<label for="time_limit">Tiempo límite (ms)</label>
			<input id='time_limit' name='time_limit' value='{if $IS_UPDATE eq 0}{$TIME_LIMIT}{/if}' type='text' class="form-control" />
		</div>

		<div class="form-group  col-md-6">
			<label for="memory_limit">Límite de memoria (kB)</label>
			<input id='memory_limit' name='memory_limit' value='{if $IS_UPDATE eq 0}{$MEMORY_LIMIT}{/if}' type='text' class="form-control" />
		</div>
	</div>
	
	<div class="row">
		<div class="form-group  col-md-6">
			<label for="output_limit">Límite de salida (bytes)</label>
			<input id="output_limit" name="output_limit" value="{if $IS_UPDATE eq 0}{$OUTPUT_LIMIT}{/if}" type='text' class="form-control" />
		</div>

		<div class="form-group  col-md-6">
			<label for="source">{#wordsSource#}</label>
			<input id='source' name='source' value='{if $IS_UPDATE eq 0}{$SOURCE|htmlspecialchars}{/if}' type='text' class="form-control" />
		</div>
	</div>
		
	<div class="row">
		<div class="form-group col-md-6">
			<label for="public">{#problemEditFormAppearsAsPublic#}</label>
			<select name='public' id='public' class="form-control">
				<option value="0">{#wordsNo#}</option>
				<option value="1" selected="selected">Sí</option>
			</select>
		</div>

		<script>
			$(function() {
				$('#languages').selectpicker();
				{if $IS_UPDATE eq 0}
					// Set default languages.
					$('#languages').val(['c','cpp','java','py','rb','pl','cs','p','kp','kj','cat','hs','cpp11']);
				{/if}
			});
		</script>
		<div class="form-group col-md-6">
			<label for="languages">{#problemEditFormLanguages#}</label>
			<select name='languages[]' id='languages' class="selectpicker form-control languages" multiple>
				<option value="c">C</option>
				<option value="cpp">C++</option>
				<option value="cpp11">C++11</option>
				<option value="cs">C#</option>
				<option value="hs">Haskell</option>
				<option value="java">Java</option>
				<option value="kj">Karel (Java)</option>
				<option value="kp">Karel (Pascal)</option>
				<option value="p">Pascal</option>
				<option value="pl">Perl</option>
				<option value="py">Python</option>
				<option value="rb">Ruby</option>
				<option value="cat">{#wordsJustOutput#}</option>
			</select>
		</div>
	</div>
	
	<input id='' name='request' value='submit' type='hidden'>
	
	{if $IS_UPDATE eq 1}
	<div class="row">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#statement-source" data-toggle="tab">Source</a></li>
			<li><a href="#statement-preview" data-toggle="tab">Preview</a></li>
		</ul>
		
		<div class="tab-content">
			<div class="tab-pane active" id="statement-source">
				<div id="wmd-button-bar-statement"></div>
				<textarea class="wmd-input" id="wmd-input-statement" name="wmd-input-statement"></textarea>
			</div>

			<div class="tab-pane" id="statement-preview">
				<div class="no-bottom-margin" id="wmd-preview-statement"></div>
			</div>
		</div>
	</div>			
	{/if}
	
	<div class="row">
		<div class="form-group col-md-6 no-bottom-margin">
		{if $IS_UPDATE eq 1}
			<button type='submit' class="btn btn-primary">{#problemEditFormUpdateProblem#}</button>	
		{else}
			<button type='submit' class="btn btn-primary">{#problemEditFormCreateProblem#}</button>	
		{/if}
		</div>
	</div>
				
</form>
	<p>
		<a href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-escribir-problemas-para-Omegaup">{#navHelp#}</a>
	</p>
{if $IS_UPDATE eq 1}
<div class="tab-pane" id="admins">
	<div class="panel panel-primary">
		<div class="panel-body">
			<form class="form" id="add-admin-form">
				<div class="form-group">
					<label for="username-admin">{#wordsAdmin#}</label>
					<input id="username-admin" name="username" value="" type="text" size="20" class="form-control" autocomplete="off" />
				</div>

				<input id="user-admin" name="user" value="" type="hidden">

				<button class="btn btn-primary" type='submit'>Agregar {#wordsAdmin#}</button>
			</form>
		</div>

		<table class="table table-striped">
			<thead>
				<th>{#contestEditRegisteredAdminUsername#}</th>
				<th>{#contestEditRegisteredAdminRole#}</th>
				<th>{#contestEditRegisteredAdminDelete#}</th>
			</thead>
			<tbody id="problem-admins"></tbody>
		</table>
	</div>
</div>
{/if}

<script>
	(function(){
		var problemAlias = '{$smarty.get.problem}';

		// Add admins typeahead
		function typeahead(dest) {
			return {
				ajax: '/api/user/list/',
				display: 'label',
				val: 'label',
				minLength: 2,
				itemSelected: function (item, val, text) {
					$(dest).val(val);
				}
			}
		};

		refreshProblemAdmins();
		$('#username-admin').typeahead(typeahead('#user-admin'));

		$('#add-admin-form').submit(function() {
			var username = $('#user-admin').val();

			omegaup.addAdminToProblem(problemAlias, username, function(response) {
				if (response.status === "ok") {
					OmegaUp.ui.success("Admin successfully added!");
					$('div.post.footer').show();

					refreshProblemAdmins();
				} else {
					OmegaUp.ui.error(response.error || 'error');
				}
			});

			return false; // Prevent refresh
		});

		function refreshProblemAdmins() {
			omegaup.getProblemAdmins(problemAlias, function(admins) {
				$('#problem-admins').empty();
				// Got the contests, lets populate the dropdown with them
				for (var i = 0; i < admins.admins.length; i++) {
					var admin = admins.admins[i];
					$('#problem-admins').append(
						$('<tr></tr>')
							.append($('<td></td>').append(
								$('<a></a>')
									.attr('href', '/profile/' + admin.username + '/')
									.text(admin.username)
							))
							.append($('<td></td>').text(admin.role))							
							.append((admin.role != "admin") ? $('<td></td>') : $('<td><button type="button" class="close">&times;</button></td>')
								.click((function(username) {
									return function(e) {
										omegaup.removeAdminFromProblem(problemAlias, username, function(response) {
											if (response.status == "ok") {
												OmegaUp.ui.success("Admin successfully removed!");
												$('div.post.footer').show();
												var tr = e.target.parentElement.parentElement;
												$(tr).remove();
											} else {
												OmegaUp.ui.error(response.error || 'error');
											}
										});
									};
								})(admin.username))
							)							
					);
				}
			});
		}
	})();
</script>
