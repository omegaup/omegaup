{if !isset($IS_UPDATE)}
	{assign "IS_UPDATE" 0}
{/if}

<div class="panel panel-primary">
	{if $IS_UPDATE neq 1}
	<div class="panel-heading">
		<h3 class="panel-title">
			{#problemNew#}
		</h3>
	</div>
	{/if}
	<div class="panel-body">
		<form method='POST' action='{$smarty.server.REQUEST_URI}' id='problem_form' class="form" enctype="multipart/form-data">
			{if $IS_UPDATE eq 1}
				<input type="hidden" name="problem_alias" value="{$smarty.get.problem}" />
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

				<div class="form-group col-md-6">
					<label for="languages">{#problemEditFormLanguages#}</label>
					<select name="languages[]" id="languages" class="form-control">
						<option value="c,cpp,cpp11,cs,hs,java,p,py,rb">C, C++, C++11, C#, Haskell, Java, Pascal, Python, Ruby</option>
						<option value="kp,kj">Karel</option>
						<option value="cat">{#wordsJustOutput#}</option>
					</select>
				</div>
			</div>
					
			<div class="row">
				<div class="form-group  col-md-6">
					<label for="stack_limit">{#problemEditStackLimit#}</label>
					<input id="stack_limit" name="stack_limit" value="{if $IS_UPDATE eq 0}{$STACK_LIMIT}{/if}" type='text' class="form-control" />
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
