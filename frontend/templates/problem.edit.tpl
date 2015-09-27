{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleProblemEdit#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<script src="/js/problem.edit.js?ver=884d97" type="text/javascript"></script>

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
	<li><a href="#download" data-toggle="tab">{#wordsDownload#}</a></li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="edit">
		{include file='problem.edit.form.tpl'}
	</div>

	<div class="tab-pane" id="markdown">
		<div class="panel panel-primary">
			<form class="panel-body form" method="post" action="{$smarty.server.REQUEST_URI}" enctype="multipart/form-data">
				<input type="hidden" name="problem_alias" id="problem-alias" value="{$smarty.get.problem}" />
				<input type="hidden" name="request" value="markdown" />
				<div class="row">
					<label for="statement-language">{#statementLanguage#}</label>
					<select name="statement-language" id="statement-language">
						<option value="es">{#statementLanguageEs#}</option>
						<option value="en">{#statementLanguageEn#}</option>
						<option value="pt">{#statementLanguagePt#}</option>
					</select>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="panel">
							<ul class="nav nav-tabs">
								<li class="active"><a href="#statement-source" data-toggle="tab">Source</a></li>
								<li><a id="statement-preview-link" href="#statement-preview" data-toggle="tab">Preview</a></li>
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
					<div class="form-group  col-md-6" id="markdown-message-group">
						<label class="control-label" for="markdown-message">{#problemEditCommitMessage#}</label>
						<input id="markdown-message" name="message" type="text" class="form-control" />
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

	<div class="tab-pane" id="download">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-tag-form">
					<div class="form-group">
						<button class="btn btn-primary" type='submit'>{#wordsDownload#}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

{include file='footer.tpl'}
