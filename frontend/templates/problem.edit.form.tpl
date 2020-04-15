<script src="{version_hash src="/js/problem.edit.form.js"}" type="text/javascript" defer></script>

<div class="panel panel-primary">
	{if $IS_UPDATE eq false}
	<div class="panel-heading">
		<h3 class="panel-title">
			{#problemNew#}
		</h3>
	</div>
	{/if}
	<div class="page-header text-center top-margin">
		<p class="no-bottom-margin">{#problemEditFormFirstTimeCreatingAProblem#}
			<strong>
				<a href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-escribir-problemas-para-Omegaup" target="_blank">{#problemEditFormHereIsHowToWriteProblems#}</a>
			</strong>
		</p>
	</div>
	<div class="panel-body">
		<form method="POST" action="{$smarty.server.REQUEST_URI}" id="problem-form" class="form" enctype="multipart/form-data">
			{if $IS_UPDATE eq true}
				<input type="hidden" name="problem_alias" value="{$smarty.get.problem}" />
			{/if}

			<div class="row">
				<div class="form-group  col-md-6" id="title-group">
					<label class="control-label" for="title">{#wordsTitle#}</label>
					<input id='title' name='title' value='{if $IS_UPDATE eq false}{$TITLE|htmlspecialchars}{/if}' type='text' class="form-control" />
				</div>

				<div class="form-group  col-md-6">
					<label class="control-label" for="alias">{#wordsAlias#}</label>
					<input id='alias' name='alias' value='{if $IS_UPDATE eq false}{$ALIAS|htmlspecialchars}{/if}' type='text' class="form-control"{if $IS_UPDATE eq true} disabled="disabled"{/if}/>
				</div>

			</div>

			<div id="problem-settings"></div>
			<script type="text/json" id="problem-payload">{$payload|json_encode}</script>
			{if $IS_UPDATE eq false}
			{js_include entrypoint="problem_settings"}
			{/if}
			<div class="row">
				<div class="form-group  col-md-{if $IS_UPDATE eq false}4{else}6{/if}" id="source-group">
					<label for="email_clarifications">{#problemEditEmailClarifications#}</label>
					<div class="form-control">
						<label class="radio-inline"><input type="radio" id="er2" name="email_clarifications" value="1" {if $IS_UPDATE eq false && $EMAIL_CLARIFICATIONS ne 0}checked="checked"{/if}>{#wordsYes#}</label>
						<label class="radio-inline"><input type="radio" id="er1" name="email_clarifications" value="0" {if $IS_UPDATE eq false && $EMAIL_CLARIFICATIONS eq 0}checked="checked"{/if}>{#wordsNo#}</label>
					</div>
				</div>

				<div class="form-group col-md-{if $IS_UPDATE eq false}4{else}6{/if}">
					<label for="visibility">{#problemEditFormAppearsAsPublic#}</label>
					<div class="form-control">
						<label class="radio-inline"><input type="radio" id="r2" name="visibility" value="1" {if $IS_UPDATE eq false && $VISIBILITY eq 1}checked=checked{/if}>{#wordsYes#}</label>
						<label class="radio-inline"><input type="radio" id="r1" name="visibility" value="0" {if $IS_UPDATE eq false && $VISIBILITY eq 0}checked=checked{/if}>{#wordsNo#}</label>
					</div>
				</div>
				{if $IS_UPDATE eq false}
				<div class="form-group col-md-4">
					<label for="allow_user_add_tags">{#problemEditFormAllowUserAddTags#}</label>
					<div class="form-control">
						<label class="radio-inline"><input type="radio" id="t2" name="allow_user_add_tags" value="1" {if $ALLOW_TAGS eq 1}checked=checked{/if}>{#wordsYes#}</label>
						<label class="radio-inline"><input type="radio" id="t1" name="allow_user_add_tags" value="0" {if $ALLOW_TAGS eq 0}checked=checked{/if}>{#wordsNo#}</label>
					</div>
				</div>
				{/if}
			</div>

			<div class="row">
				<div class="form-group  col-md-6">
					<label class="control-label" for="source">{#problemEditSource#}</label>
					<input id='source' name='source' value='{if $IS_UPDATE eq false}{$SOURCE|htmlspecialchars}{/if}' type='text' class="form-control" />
				</div>

				<div class="form-group col-md-6" id="problem-contents-group">
					<label for="problem_contents" class="control-label">{#problemEditFormFile#}</label>
					<a href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-escribir-problemas-para-Omegaup" target="_blank"><span>{#problemEditFormHowToWriteProblems#}</span></a>
					<input name="problem_contents" id="problem_contents" type="file" class="form-control" />
				</div>
			</div>

			{if $IS_UPDATE eq false}
			<div class="panel panel-primary">
				<div class="panel-body">
					<div class="form-group">
						<label for="tag-name">{#wordsTags#}</label>
					</div>
					<div class="form-group">
						<div class="tag-list pull-left"></div>
					</div>
					<div class="form-group">
						<label for="tag-public">{#problemEditTagPublic#}</label>
						<select id="tag-public" class="form-control">
							<option value="false" selected="selected">{#wordsNo#}</option>
							<option value="true">{#wordsYes#}</option>
						</select>
					</div>
				</div>

				<table class="table table-striped">
					<thead>
						<tr>
							<th>{#contestEditTagName#}</th>
							<th>{#contestEditTagPublic#}</th>
							<th>{#contestEditTagDelete#}</th>
						</tr>
					</thead>
					<tbody id="problem-tags"></tbody>
				</table>
				<input type="hidden" name="selected_tags" />
			</div>
			{/if}

      {if $IS_UPDATE eq true}
			<div class="row">
				<div class="form-group  col-md-12" id="update-message-group">
					<label class="control-label" for="update-message">{#problemEditCommitMessage#}</label>
					<input id="update-message" name="message" type="text" class="form-control" />
				</div>
			</div>
      {/if}

			<input id='' name='request' value='submit' type='hidden'>

			<div class="row">
				<div class="form-group col-md-6 no-bottom-margin">
				{if $IS_UPDATE eq true}
					<button type='submit' class="btn btn-primary">{#problemEditFormUpdateProblem#}</button>
				{else}
					<button type='submit' class="btn btn-primary">{#problemEditFormCreateProblem#}</button>
				{/if}
				</div>
			</div>
		</form>
	</div>
</div>
