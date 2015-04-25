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
	<li><a href="#tags" data-toggle="tab">{#problemEditAddTags#}</a></li>
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

	<div class="tab-pane" id="tags">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-tag-form">
					<div class="form-group">
						<label for="tag-name">{#wordsTags#}</label>
						<input id="tag-name" name="tag_name" value="" type="text" size="20" class="form-control" autocomplete="off" />
					</div>
					<div class="form-group">
						<label for="tag-public">{#wordsPublic#}</label>
						<select id="tag-public" name="tag_public" class="form-control">
							<option value="0" selected="selected">{#wordsNo#}</option>
							<option value="1">{#wordsYes#}</option>
						</select>
					</div>

					<button class="btn btn-primary" type='submit'>{#wordsAddTag#}</button>
				</form>
			</div>

			<table class="table table-striped">
				<thead>
					<th>{#contestEditTagName#}</th>
					<th>{#contestEditTagPublic#}</th>
					<th>{#contestEditTagDelete#}</th>
				</thead>
				<tbody id="problem-tags"></tbody>
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

		// Add typeaheads
		refreshProblemAdmins();
		$("#username-admin").typeahead({
			minLength: 2,
			highlight: true,
		}, {
			source: omegaup.searchUsers,
			displayKey: 'label',
		}).on('typeahead:selected', function(item, val, text) {
			$("#username-admin").val(val.label);
		});

		refreshProblemTags();
		$("#tag-name").typeahead({
			minLength: 2,
			highlight: true,
		}, {
			source: omegaup.searchTags,
			displayKey: 'name',
		}).on('typeahead:selected', function(item, val, text) {
			$("#tag-name").val(val.name);
		});

		$('#add-admin-form').submit(function() {
			var username = $('#username-admin').val();

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

		$('#add-tag-form').submit(function() {
			var tagname = $('#tag-name').val();
			var public = $('#tag-public').val();

			omegaup.addTagToProblem(problemAlias, tagname, public, function(response) {
				if (response.status === "ok") {
					OmegaUp.ui.success("Tag successfully added!");
					$('div.post.footer').show();

					refreshProblemTags();
				} else {
					OmegaUp.ui.error(response.error || 'error');
				}
			});

			return false; // Prevent refresh
		});

		function refreshProblemTags() {
			omegaup.getProblemTags(problemAlias, function(result) {
				$('#problem-tags').empty();
				// Got the contests, lets populate the dropdown with them
				for (var i = 0; i < result.tags.length; i++) {
					var tag = result.tags[i];
					$('#problem-tags').append(
						$('<tr></tr>')
							.append($('<td></td>').append(
								$('<a></a>')
									.attr('href', '/problem/?tag=' + tag.name)
									.text(tag.name)
							))
							.append($('<td></td>').text(tag.public))
							.append($('<td><button type="button" class="close">&times;</button></td>')
								.click((function(tagname) {
									return function(e) {
										omegaup.removeTagFromProblem(problemAlias, tagname, function(response) {
											if (response.status == "ok") {
												OmegaUp.ui.success("Tag successfully removed!");
												$('div.post.footer').show();
												var tr = e.target.parentElement.parentElement;
												$(tr).remove();
											} else {
												OmegaUp.ui.error(response.error || 'error');
											}
										});
									};
								})(tag.name))
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
			$('input[name=validator_time_limit]').val('');
			$('input[name=overall_wall_time_limit]').val('');
			$('input[name=extra_wall_time]').val('');
			$('input[name=memory_limit]').val('');
			$('input[name=output_limit]').val('');
			$('input[name=source]').val('');
			$('input[name=stack_limit]').val('');
			return;
		}
		
		omegaup.getProblem(null, problemAlias, function(problem) {
			$('.page-header h1 span').html('{#problemEditEditProblem#} ' + problem.title);
			$('.page-header h1 small').html('&ndash; <a href="/arena/problem/' + problemAlias + '/">{#problemEditGoToProblem#}</a>');
			$('input[name=title]').val(problem.title);
			$('#statement-preview .title').html(omegaup.escape(problem.title));
			$('input[name=time_limit]').val(problem.time_limit);
			$('input[name=validator_time_limit]').val(problem.validator_time_limit);
			$('input[name=overall_wall_time_limit]').val(problem.overall_wall_time_limit);
			$('input[name=extra_wall_time]').val(problem.extra_wall_time);
			$('input[name=memory_limit]').val(problem.memory_limit);
			$('input[name=output_limit]').val(problem.output_limit);
			$('input[name=stack_limit]').val(problem.stack_limit);
			$('input[name=source]').val(problem.source);
			$('#statement-preview .source').html(omegaup.escape(problem.source));
			$('select[name=validator]').val(problem.validator);
			$('select[name=public]').val(problem.public);
			$('#languages').val(problem.languages);
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
