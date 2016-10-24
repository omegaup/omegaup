{if !isset($IS_UPDATE)}
	{assign "IS_UPDATE" 0}
{/if}

<script src="{version_hash src="/js/problem.edit.form.js"}" type="text/javascript"></script>

<div class="panel panel-primary">
	{if $IS_UPDATE != 1}
	<div class="panel-heading">
		<h3 class="panel-title">
			{#problemNew#}
		</h3>
	</div>
	{/if}
	<div class="panel-body">
		<form method="POST" action="{$smarty.server.REQUEST_URI}" id="problem-form" class="form" enctype="multipart/form-data">
			{if $IS_UPDATE eq 1}
				<input type="hidden" name="problem_alias" value="{$smarty.get.problem}" />
			{/if}

			<div class="row">
				<div class="form-group  col-md-6" id="title-group">
					<label class="control-label" for="title">{#wordsTitle#}</label>
					<input id='title' name='title' value='{if $IS_UPDATE eq 0}{$TITLE|htmlspecialchars}{/if}' type='text' class="form-control" />
				</div>

				<div class="form-group  col-md-6">
					<label class="control-label" for="alias">{#wordsAlias#}</label>
					<input id='alias' name='alias' value='{if $IS_UPDATE eq 0}{$ALIAS|htmlspecialchars}{/if}' type='text' class="form-control"{if $IS_UPDATE eq 1} disabled="disabled"{/if}/>
				</div>

			</div>

			<div class="row">
				<div class="form-group col-md-6">
					<label for="validator">{#problemEditFormValidatorType#}</label>
					<select name='validator' id='validator' class="form-control" >
							<option value="token-caseless">{#problemEditFormTokenCaseless#}</option>
							<option value="token-numeric">{#problemEditFormNumericTokensWithTolerance#}</option>
							<option value="token">{#problemEditFormTokenByToken#}</option>
							<option value="literal">{#problemEditFormLiteral#}</option>
							<option value="custom">{#problemEditFormCustom#}</option>
					</select>
				</div>

				<div class="form-group  col-md-6">
					<label for="validator_time_limit">{#problemEditFormValidatorTimeLimit#}</label>
					<input id='validator_time_limit' name='validator_time_limit' value='{if $IS_UPDATE eq 0}{$VALIDATOR_TIME_LIMIT}{/if}' type='text' class="form-control" />
				</div>
			</div>

			<div class="row">
				<div class="form-group  col-md-6">
					<label for="time_limit">{#problemEditFormTimeLimit#}</label>
					<input id='time_limit' name='time_limit' value='{if $IS_UPDATE eq 0}{$TIME_LIMIT}{/if}' type='text' class="form-control" />
				</div>

				<div class="form-group col-md-6">
					<label for="overall_wall_time_limit">{#problemEditFormWallTimeLimit#}</label>
					<input id='overall_wall_time_limit' name='overall_wall_time_limit' value='{if $IS_UPDATE eq 0}{$OVERALL_WALL_TIME_LIMIT}{/if}' type='text' class="form-control" />
				</div>

			</div>

			<div class="row">
				<div class="form-group  col-md-6">
					<label for="stack_limit">{#problemEditStackLimit#}</label>
					<input id="stack_limit" name="stack_limit" value="{if $IS_UPDATE eq 0}{$STACK_LIMIT}{/if}" type='text' class="form-control" />
				</div>

				<div class="form-group col-md-6">
					<label for="extra_wall_time">{#wordsExtraWallTimeMs#}</label>
					<input id='extra_wall_time' name='extra_wall_time' value='{if $IS_UPDATE eq 0}{$EXTRA_WALL_TIME}{/if}' type='text' class="form-control" />
				</div>
			</div>

			<div class="row">
				<div class="form-group  col-md-6">
					<label for="memory_limit">{#problemEditFormMemoryLimit#}</label>
					<input id='memory_limit' name='memory_limit' value='{if $IS_UPDATE eq 0}{$MEMORY_LIMIT}{/if}' type='text' class="form-control" />
				</div>

				<div class="form-group  col-md-6">
					<label for="output_limit">{#problemEditFormOutputLimit#}</label>
					<input id="output_limit" name="output_limit" value="{if $IS_UPDATE eq 0}{$OUTPUT_LIMIT}{/if}" type='text' class="form-control" />
				</div>
			</div>

			<div class="row">
				<div class="form-group  col-md-6" id="source-group">
					<label class="control-label" for="source">{#wordsSource#}</label>
					<input id='source' name='source' value='{if $IS_UPDATE eq 0}{$SOURCE|htmlspecialchars}{/if}' type='text' class="form-control" />
				</div>

				<div class="form-group col-md-6">
					<label for="languages">{#problemEditFormLanguages#}</label>
					<select name="languages[]" id="languages" class="form-control">
						<option value="c,cpp,cpp11,cs,hs,java,pas,py,rb">C, C++, C++11, C#, Haskell, Java, Pascal, Python, Ruby</option>
						<option value="kp,kj">Karel</option>
						<option value="cat">{#wordsJustOutput#}</option>
					</select>
				</div>

			</div>

			<div class="row">
				<div class="form-group  col-md-6">
					<label for="email_clarifications">{#problemEditEmailClarifications#}</label>
					<div class="form-control">
						<label class="radio-inline"><input type="radio" id="er2" name="email_clarifications" value="1" {if $IS_UPDATE eq 0 && $EMAIL_CLARIFICATIONS eq 1}checked="checked"{/if}>{#wordsYes#}</label>
						<label class="radio-inline"><input type="radio" id="er1" name="email_clarifications" value="0" {if $IS_UPDATE eq 0 && $EMAIL_CLARIFICATIONS eq 0}checked="checked"{/if}>{#wordsNo#}</label>
					</div>
				</div>

				{if $IS_UPDATE eq 1}
				<div class="form-group  col-md-6" id="update-message-group">
					<label class="control-label" for="update-message">{#problemEditCommitMessage#}</label>
					<input id="update-message" name="message" type="text" class="form-control" />
				</div>
				{/if}

				<div class="form-group col-md-6">
					<label for="public">{#problemEditFormAppearsAsPublic#}</label>
					<div class="form-control">
						<label class="radio-inline"><input type="radio" id="r2" name="public" value="1" {if $IS_UPDATE eq 0}checked=checked{/if}>{#wordsYes#}</label>
						<label class="radio-inline"><input type="radio" id="r1" name="public" value="0">{#wordsNo#}</label>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="form-group col-md-6" id="problem-contents-group">
					<label for="problem_contents" class="control-label">{#problemEditFormFile#}</label>
					<a href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-escribir-problemas-para-Omegaup" target="_blank"><span>{#problemEditFormHowToWriteProblems#}</span></a>
					<input name="problem_contents" id="problem_contents" type="file" class="form-control" />
				</div>
			</div>

			<input id='' name='request' value='submit' type='hidden'>

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
	</div>
</div>
