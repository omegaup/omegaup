<form method='POST' action='{$smarty.server.REQUEST_URI}' id='problem_form' enctype="multipart/form-data">			
	
	{if $IS_UPDATE eq 1}
		<legend>Problema: <select class="edit-problem-list" name='edit-problem-list' id='problem_alias'>
				<option></option>
		</select></legend>
	{/if}

	<legend>Archivo: <input name="problem_contents" type="file" /></legend>
	<legend>Título: <input id='title' name='title' value='{$TITLE}' type='text'></legend>
	<legend>Tipo de validador: <select name='validator' id='validator'>
			<option value="token-caseless">Token por Token, ignorando diferencias en mayúsculas/minúsculas (default)</option>
			<option value="token-numeric">Tokens numéricos con tolerancia</option>		
			<option value="token">Token por Token</option>		
			<option value="literal">Sólo salida, comparación literal</option>				
		</select></legend>
	<legend>Tiempo límite: (ms)<input id='time_limit' name='time_limit' value='{$TIME_LIMIT}' type='text'></legend>
	<legend>Memory limit: (KB)<input id='memory_limit' name='memory_limit' value='{$MEMORY_LIMIT}' type='text'></legend>
	<legend>Fuente: <input id='source' name='source' value='{$SOURCE}' type='text'>	</legend>
	<legend>Aparece en el listado público: <select name='public' id='public'>
			<option value="0">No</option>
			<option value="1">Sí</option>
		</select></legend>
	<input id='' name='request' value='submit' type='hidden'>
	
	{if $IS_UPDATE eq 1}
		<input value='Actualizar problema' type='submit' class="OK">			
	{else}
		<input value='Crear problema' type='submit' class="OK">			
	{/if}
	
</form>