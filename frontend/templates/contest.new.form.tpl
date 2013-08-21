<div class="post">
	<div class="copy">
		{if $IS_UPDATE neq 1}
		<h3>Nuevo concurso</h3>
		<div>
			<div class="POS Boton" id='omi' name='omi'>Estilo OMI - IOI</div>
			<div class="POS Boton" id='preioi' name='preioi'>Estilo Preselectivo IOI</div>
			<div class="POS Boton" id='conacup' name='conacup'>Estilo CONACUP</div>
		</div>		
		{else}
			<h3>Editar concurso</h3>
		{/if}
		<form class="new_contest_form">
			<table id="main" width="100%">
				{if $IS_UPDATE eq 1}
				<tr>
					<td class="info">
						<b>Concurso a editar:</b>
					</td>
					<td>
						<select class='contests' name='contests' id='contest_alias'>
							<option value=""></option>				
						</select>
					</td>
				</tr>
				{/if}
				<tr>
					<!-- ----------------------------------------- -->
					<td class="info">
						<b>Título</b>
						<p>							
						</p>
					</td>
					<td>
						<input id='title' name='title' value='' type='text' size='30'>
					</td>
					<td class="info">
						<b>Título corto (alias):</b>
						<p>
							El título corto se usa para construir la URL del concurso (ejemplos: ANPA2010, CONACUP2012, OMI2013, etc..). No puede contener espacios.
						</p>
					</td>
					<td>
						<input id='alias' name='alias' value='' type='text' {IF $IS_UPDATE eq 1} disabled="true" {/if}>
					</td>
				</tr>
				<!-- ----------------------------------------- -->
				<tr>
					<!-- ----------------------------------------- -->
					<td class="info">
						<b>Fecha de Inicio</b>
						<p>
							La fecha (en hora local) en la que inicia el concurso
						</p>
					</td>
					<td>
						<input id='start_time' name='start_time' value='' type='text' size ='16'>
					</td>
					<td class="info">
						<b>Fecha de Fin</b>
						<p>
							La hora (en hora local) en la que termina el concurso.
						</p>
					</td>
					<td>
						<input id='finish_time' name='finish_time' value='' type='text' size='16'>
					</td>
				</tr>
				<!-- ----------------------------------------- -->
				<tr>
					<!-- ----------------------------------------- -->
					<td class="info">
						<b>Descripci&oacute;n del concurso</b>
						<p>
						</p>
					</td>
					<td>
						<textarea id='description' name='description' cols="30" rows="10"></textarea>
					</td>
					<td class="info">
						<b><input type='checkbox' id='window_length_enabled' name='window_length_enabled'> Inicios Diferentes</b>
						<p>
							Si está activo, indica el tiempo en minutos que tiene el usuario para concursar y env&iacute;ar soluciones a partir de que entra al concurso en la arena (estilo USACO/Preselectivo IOI). 
							Si Inicios Diferentes está desactivado, entonces el concursante tendrá todo el tiempo entre la Fecha de Inicio y la Fecha de Fin para concursar.
						</p>
					</td>
					<td>
						<input id='window_length' name='window_length' value='' type='text' disabled="true" size='3'>
					</td>
				</tr>
				<!-- ----------------------------------------- -->
				<tr>
					<!-- ----------------------------------------- -->
					<td class="info">
						<b>¿Cuánto tiempo se mostrará el Scoreboard? (%)</b>
						<p>
							Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard ser&aacute; visible.
						</p>
					</td>
					<td>
						<input id='scoreboard' name='scoreboard' value='100' type='text' size='3'>
					</td>
					<td class="info">
						<b>Separación de envios</b>
						<p>
							Tiempo m&iacute;nimo en minutos que debe de esperar un concursante despues de realizar un env&iacute;o para hacer otro.
						</p>
					</td>
					<td>
						<input id='submissions_gap' name='submissions_gap' value='1' type='text' size='2'>
					</td>
				</tr>
				<!-- ----------------------------------------- -->
				<tr>
					<!-- ----------------------------------------- -->
					<td class="info">
						<b>Tipo de Penalty</b>
						<p>
							Indica el momento cuando se inicia a contar el tiempo: cuando inicia el concurso o cuando se abre el problema.
						</p>
					</td>
					<td>
						<select name='penalty_time_start' id='penalty_time_start'>
							<option value='none'>Sin Penalty</option>
							<option value='problem'>Por problema</option>
							<option value='contest'>Por concurso</option>
						</select>
					</td>
					<td class="info">
						<b>Penalty</b>
						<p>
							Entero indicando el n&uacute;mero de minutos con que se penaliza por enviar una respuesta incorrecta.
						</p>
					</td>
					<td>
						<input id='penalty' name='penalty' value='0' type='text' size='2'>
					</td>
				</tr>
				<!-- ----------------------------------------- -->
				<tr>
					<!-- ----------------------------------------- -->
					<td class="info">
						<b>Feedback</b>
						<p>
							Si al usuario se le entrega retroalimentación inmediata sobre su problema
						</p>
					</td>
					<td>
						<select name='feedback' id='feedback'>
							<option value='yes'>Sí</option>
							<option value='no'>No</option>
							<option value='partial'>Parcial</option>
						</select>
					</td>
					<td class="info">
						<b>Factor de Decrecimiento de Puntaje</b>
						<p>
							Un número entre 0 y 1 inclusive. Si este número es distinto de cero, el puntaje que se obtiene al resolver correctamente un problema decae conforme pasa el tiempo. El valor del puntaje estará dado por (1 - points_decay_factor) + points_decay_factor * TT^2 / (10 * PT^2 + TT^2), donde PT es el penalty en minutos del envío y TT el tiempo total del concurso, en minutos.
						</p>
					</td>
					<td>
						<input id='points_decay_factor' name='points_decay_factor' value='0.0' type='text' size='4'>
					</td>
				</tr>
				<!-- ----------------------------------------- -->
				<tr>
					<!-- ----------------------------------------- -->
					
					<td class="info">
						<b>Scoreboard al finalizar el concurso</b>
						<p>
							Mostrar automáticamente el scoreboard completo al finalizar el concurso.
						</p>
					</td>
					<td>
						<select id='show_scoreboard_after' name='show_scoreboard_after'>
							<option value='1'>Sí</option>
							<option value='0'>No</option>
						</select>
					</td>
					<td class="info">
						<b>Público</b>
						<p>
							Mostrar el concurso en el listado público.
						</p>
					</td>
					<td>
						<select name='public' id='public'>
							<option value='1'>Sí</option>
							<option value='0'>No</option>
						</select>
					</td>
				</tr>
				<!-- ----------------------------------------- -->
				<tr>
					<!-- ----------------------------------------- -->
					<td class="info">
						<b></b>
						<p>
						</p>
					</td>
					<td>
					</td>
					
				</tr>
				<!-- ----------------------------------------- -->
				<tr>
					<!-- ----------------------------------------- -->
					<td>
					</td>
					<td>
					</td>
					<td align='right'>
						{if $IS_UPDATE eq 1}
							<input value='Actualizar concurso' type='submit' class="OK">
						{else}
							<input value='Agendar concurso' type='submit' class="OK">
						{/if}
					</td>
				</tr>
				<!-- ----------------------------------------- -->
			</table>
			<!--
			<div id="submit-wrapper">
				<div id="response">
				</div>
			</div>
			-->
		</form>
	</div>
</div>
<script>		
	
	$("#start_time, #finish_time").datetimepicker();
	
	{IF $IS_UPDATE neq 1}
		// Defaults for start_time and end_time	
		var defaultDate = new Date(Date.now());
		$('#start_time').val(dateToString(defaultDate));	
		defaultDate.setHours(defaultDate.getHours() + 5);
		$('#finish_time').val(dateToString(defaultDate));
	{/IF}
			
	
	// Defaults for OMI
	$('#omi').click(function() {
		$(".new_contest_form #title").val('**Estilo OMI aplicado. Pon tu título aquí**');
		$('#window_length_enabled').removeAttr('checked');
		$('#window_length').attr('disabled','disabled');
		$('#window_length').val('');
		
		$(".new_contest_form #public").val('1');
		$(".new_contest_form #scoreboard").val('0');
		$(".new_contest_form #points_decay_factor").val('0');		
		$(".new_contest_form #submissions_gap").val('1');
		$(".new_contest_form #feedback").val('yes');
		$(".new_contest_form #penalty").val('0');
		$(".new_contest_form #penalty_time_start").val('none');
		$(".new_contest_form #show_scoreboard_after").val('1');		
	});
	
	// Defaults for preselectivos IOI
	$('#preioi').click(function() {
		$(".new_contest_form #title").val('**Estilo Preselectivo aplicado. Pon tu título aquí**');
		$('#window_length_enabled').attr('checked', 'checked');
		$('#window_length').removeAttr('disabled');
		$('#window_length').val('180');
		
		$(".new_contest_form #public").val('1');
		$(".new_contest_form #scoreboard").val('0');
		$(".new_contest_form #points_decay_factor").val('0');		
		$(".new_contest_form #submissions_gap").val('0');
		$(".new_contest_form #feedback").val('yes');
		$(".new_contest_form #penalty").val('0');
		$(".new_contest_form #penalty_time_start").val('none');
		$(".new_contest_form #show_scoreboard_after").val('1');		
	});
	
	// Defaults for CONACUP
	$('#conacup').click(function() {
		$(".new_contest_form #title").val('**Estilo CONCACUP aplicado. Tu título aquí**');
		$('#window_length_enabled').removeAttr('checked');
		$('#window_length').attr('disabled','disabled');
		$('#window_length').val('');
		
		$(".new_contest_form #public").val('1');
		$(".new_contest_form #scoreboard").val('75');
		$(".new_contest_form #points_decay_factor").val('0');		
		$(".new_contest_form #submissions_gap").val('1');
		$(".new_contest_form #feedback").val('yes');
		$(".new_contest_form #penalty").val('20');
		$(".new_contest_form #penalty_time_start").val('contest');
		$(".new_contest_form #show_scoreboard_after").val('1');		
	});
</script>