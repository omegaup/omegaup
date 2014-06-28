{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleProblemEdit#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="alert alert-warning slow-warning" style="display: none;">{#problemEditSlowWarning#}</div>

<div class="page-header">
	<h1><span>{#frontPageLoading#}</span> <small></small></h1>
	<p><a href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-escribir-problemas-para-Omegaup">{#navHelp#}</a></p>
</div>

<ul class="nav nav-tabs nav-justified" id="sections">
	<li class="active"><a href="#edit" data-toggle="tab">{#problemEditEditProblem#}</a></li>
	<li><a href="#markdown" data-toggle="tab">{#problemEditEditMarkdown#}</a></li>
	<li><a href="#admins" data-toggle="tab">{#problemEditAddAdmin#}</a></li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="edit">
		{include file='problem.edit.form.tpl'}
	</div>

	<div class="tab-pane" id="markdown">
		<div class="panel panel-primary">
			<form class="panel-body form" method="post" action="{$smarty.server.REQUEST_URI}" enctype="multipart/form-data">
				<input type="hidden" name="problem_alias" value="{$smarty.get.problem}" />
				<input type="hidden" name="request" value="markdown" />
				<div class="row">
					<div class="col-md-12">
						<div class="panel">
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
									<h1 style="text-align: center;" class="title"></h1>
									<div class="no-bottom-margin statement" id="wmd-preview-statement"></div>
									<hr/>
									<em>{#wordsSource#}: <span class="source"></span></em>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<button type='submit' class="btn btn-primary">{#problemEditFormUpdateMarkdown#}</button>	
					</div>
				</div>
			</form>
		</div>
	</div>

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
</div>
			
<script>
	(function(){
		if(window.location.hash){
			$('#sections').find('a[href="'+window.location.hash+'"]').tab('show');
		}

		$('#sections').on('click', 'a', function (e) {
			e.preventDefault();
			// add this line
			window.location.hash = $(this).attr('href');
			$(this).tab('show');
		});

		var problemAlias = '{$smarty.get.problem}';
		refreshEditForm(problemAlias);

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
	
		var md_converter = Markdown.getSanitizingConverter();
		md_editor = new Markdown.Editor(md_converter, '-statement');		// Global.
		md_editor.hooks.chain("onPreviewRefresh", function() {ldelim}
			MathJax.Hub.Queue(["Typeset", MathJax.Hub, $('#wmd-preview').get(0)]);
		{rdelim});
		md_editor.run();
	})();
	
	function refreshEditForm(problemAlias) {
		if (problemAlias === "") {
			$('input[name=title]').val('');
			$('input[name=time_limit]').val('');
			$('input[name=memory_limit]').val('');
			$('input[name=output_limit]').val('');
			$('input[name=source]').val('');
			return;
		}
		
		omegaup.getProblem(null, problemAlias, function(problem) {
			$('.page-header h1 span').html('{#problemEditEditProblem#} ' + problem.title);
			$('.page-header h1 small').html('&ndash; <a href="/arena/problem/' + problemAlias + '/">{#problemEditGoToProblem#}</a>');
			$('input[name=title]').val(problem.title);
			$('#statement-preview .title').html(omegaup.escape(problem.title));
			$('input[name=time_limit]').val(problem.time_limit);
			$('input[name=memory_limit]').val(problem.memory_limit);
			$('input[name=output_limit]').val(problem.output_limit);
			$('input[name=source]').val(problem.source);
			$('#statement-preview .source').html(omegaup.escape(problem.source));
			$('select[name=validator]').val(problem.validator);
			$('select[name=public]').val(problem.public);
			$('#languages')
				.val(problem.languages.split(','))
				.selectpicker('refresh');
			$('input[name=alias]').val(problemAlias);
			$('#wmd-input-statement').val(problem.problem_statement);
			md_editor.refreshPreview();
			if (problem.slow == 1) {
				$('.slow-warning').show();
			}
		}, "markdown");
	}
</script>

{include file='footer.tpl'}
