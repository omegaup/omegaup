{include file='head.tpl' htmlTitle="libinteractive"}

<div class="panel panel-default">
  <div class="panel-body">
		<form action="/libinteractive/gen/" method="post">
			<div class="form-group{if isset($error_field) && $error_field == 'language'} has-error{/if}">
				<label for="language">{#libinteractiveLanguage#}</label>
				<select class="form-control" id="language" name="language">
					<option value="cpp">C++</option>
					<option value="c">C</option>
					<option value="java">Java</option>
				</select>
			</div>
			<div class="form-group{if isset($error_field) && $error_field == 'os'} has-error{/if}">
				<label for="os">{#libinteractiveOs#}</label>
				<select class="form-control" id="os" name="os">
					<option value="windows">Windows</option>
					<option value="unix">Linux/Mac OS</option>
				</select>
			</div>
			<div class="form-group{if isset($error_field) && $error_field == 'name'} has-error{/if}">
				<label for="name">{#libinteractiveIdlFilename#}</label>
				<input type="text" class="form-control" id="name" name="name" value="{if isset($smarty.post.name)}{$smarty.post.name|escape}{/if}" />
				<p>{#libinteractiveIdlFilenameHelp#}</p>
			</div>
			<div class="form-group{if isset($error_field) && $error_field == 'idl'} has-error{/if}">
				<label for="idl">IDL</label>
				<textarea class="form-control" rows="10" name="idl">{if isset($smarty.post.idl)}{$smarty.post.idl|escape}{/if}</textarea>
			</div>
			<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-download"></span> {#wordsDownload#}</button>
		</form>
	</div>
{if isset($error)}
		<div class="panel-footer">
			<pre>{$error|escape}</pre>
		</div>
{/if}
</div>

{include file='footer.tpl'}
