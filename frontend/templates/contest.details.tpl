{assign var="htmlTitle" value="{#omegaupTitleContestDetails#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}

<script type="text/javascript" src="/js/contest.details.js?ver=8c936a"></script>

<div class="post hiddeable_on_error">
	<div class="copy wait_for_ajax" id="contest_details" >
		<h2>{#contestDetailsContestDetails#}</h2>
		<table id="main" width="100%">
			<tr>
			<!-- ----------------------------------------- -->
				<td class="info">
					<b>{#wordsTitle#}</b>
					<p>
					 El titulo que tendrá el concurso
								</p>
							</td>
							<td>
								<input id='title' name='title' value='' type='text'>
							</td>
							<td class="info">
								<b>{#wordsAlias#}</b>
								<p>
									Almacenar&aacute; el token necesario para acceder al concurso
								</p>
							</td>
							<td>
								<input id='alias' name='alias' value='' type='text'>
							</td>
						</tr>
						<!-- ----------------------------------------- -->
						<tr>
							<!-- ----------------------------------------- -->
							<td class="info">
								<b>{#wordsStartTime#}</b>
								<p>
									La fecha (en hora local) en la que inicia el concurso
								</p>
							</td>
							<td>
								<input id='start_time' name='start_time' value='1359702610' type='text'>
							</td>
							<td class="info">
								<b>{#wordsEnd#}</b>
								<p>
									La hora (en hora local) en la que termina el concurso.
								</p>
							</td>
							<td>
								<input id='finish_time' name='finish_time' value='1359749410' type='text'>
							</td>
						</tr>
						<!-- ----------------------------------------- -->
						<tr>
							<td colspan=2>
							</td>
							<td colspan=2>
								
							</td>
						</tr>
						<tr>
							<td colspan=4>
								<hr>
							</td>
						</tr>
						<tr>
							<!-- ----------------------------------------- -->
							<td class="info">
								<b>Descripci&oacute;n</b>
								<p>
								</p>
							</td>
							<td>
								<textarea id='description' name='description'></textarea>
							</td>
							<td class="info">
								<b>{#contestDetailsWindowLength#}</b>
								<p>
									Indica el tiempo que tiene el {#wordsUser#} para env&iacute;ar soluci&oacute;n, si es NULL entonces ser&aacute; durante todo el tiempo del concurso.
								</p>
							</td>
							<td>
								<input id='window_length' name='window_length' value='0' type='text'>
							</td>
						</tr>
						<!-- ----------------------------------------- -->
						<tr>
							<!-- ----------------------------------------- -->
							<td class="info">
								<b>{#contestDetailsScoreboard#}</b>
								<p>
									Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard ser&aacute; visible
								</p>
							</td>
							<td>
								<input id='scoreboard' name='scoreboard' value='100' type='text'>
							</td>
							<td class="info">
								<b>{#contestDetailsSubmissionsGap#}</b>
								<p>
									Tiempo m&iacute;nimo en minutos que debe de esperar un {#wordsUser#} despues de realizar un env&iacute;o para hacer otro.
								</p>
							</td>
							<td>
								<input id='submissions_gap' name='submissions_gap' value='1' type='text'>
							</td>
						</tr>
						<!-- ----------------------------------------- -->
						<tr>
							<!-- ----------------------------------------- -->
							<td class="info">
								<b>{#contestDetailsPenaltyType#}</b>
								<p>{#contestNewFormPenaltyTypeDesc#}</p>
							</td>
							<td>
								<select name='penalty_type' id='penalty_type' class="form-control">
									<option value='none'>{#contestNewFormNoPenalty#}</option>
									<option value='problem_open'>{#contestNewFormByProblem#}</option>
									<option value='contest_start'>{#contestNewFormByContests#}</option>
									<option value='runtime'>{#contestNewFormByRuntime#}</option>
								</select>
							</td>
							<td class="info">
								<b>{#wordsPenalty#}</b>
								<p>
									 Entero indicando el n&uacute;mero de minutos con que se penaliza por recibir un no-accepted
								</p>
							</td>
							<td>
								<input id='penalty' name='penalty' value='0' type='text'>
							</td>
						</tr>
						<!-- ----------------------------------------- -->
						<tr>
							<!-- ----------------------------------------- -->
							<td class="info">
								<b>{#wordsFeedback#}</b>
								<p>
									{#contestDetailsYes#} al {#wordsUser#} se le entrega retroalimentación inmediata sobre su problema
								</p>
							</td>
							<td>
								<select name='feedback' id='feedback'>
									<option value='yes'>{#contestDetailsYes#}</option>
									<option value='no'>{#wordsNo#}</option>
									<option value='partial'>{#wordsPartial#}</option>
								</select>
							</td>
							<td class="info">
								<b>{#contestDetailsPartialScore#}</b>
								<p>
									 Verdadero si el {#wordsUser#} recibir&aacute; puntaje parcial para problemas no resueltos en {#wordsAll#} los casos
								</p>
							</td>
							<td>
								<select name="partial_score" id="partial_score">
									<option value="0">{#wordsNo#}</option>
									<option value="1">{#contestDetailsYes#}</option>
								</select>
							</td>
						</tr>
						<!-- ----------------------------------------- -->
						<tr>
							<!-- ----------------------------------------- -->
							<td class="info">
								<b>points_decay_factor</b>
								<p>
								</p>
							</td>
							<td>
								<input id='points_decay_factor' name='points_decay_factor' value='0' type='text'>
							</td>
							<td class="info">
								<b>penalty_calc_policy</b>
								<p>
								</p>
							</td>
							<td>
								<select name='penalty_calc_policy' id='penalty_calc_policy'>
									<option value='sum'>{#contestDetailsSum#}</option>
									<option value='max'>{#contestDetailsMax#}</option>
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
							<td class="info">
								<b>public</b>
								<p>
								</p>
							</td>
							<td>
								<select name='public' id='public'>
									<option value='1'>si</option>
									<option value='0'>no</option>
								</select>
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
								
							</td>
						</tr>
						<!-- ----------------------------------------- -->
						</table>
	</div>
</div>

<div class="post hiddeable_on_error" >
	<div class="copy wait_for_ajax" id="problem_details">
		<h2>problemas en este concurso</h2>
		<div class="POS Boton" onClick="$('#problem_upload_window').toggle()">{#wordsAddProblem#}</div>
	</div>
</div>

<div class="post hiddeable_on_error" style="display:none;" id="problem_upload_window">
	<div class="copy">
		<!-- <progress></progress> -->
		<h2>{#contestDetailsNewProblem#}</h2>

		<form enctype="multipart/form-data" id="newProbForm">
		<table id="newprob" width="100%">
		<tr>
		<td class="info">
			<b>{#wordsTitle#}</b>
			<p>El titulo que tendr&aacute; el problema</p>
		</td>
		<td>
			<input id="title" type="text">
		</td>
		<td class="info">
			<b>{#wordsAlias#}</b>
			<p>Almacenar&aacute; el token necesario para acceder al problema</p>
		</td>
		<td>
			<input id='alias' name='alias' value='' type='text'>
		</td>
		</tr>
		<tr>
		<td class="info">
			<b>Public</b>
			
		</td>
		<td>
			<select id="public">
				<option value="1">{#contestDetailsYes#}</option>
				<option value="0">{#wordsNo#}</option>
			</select>
		</td>
		<td class="info">
			<b>{#wordsValidator#}</b>
			
		</td>
		<td>
			<select id="validator">
				<option>token</option>
				<option>token-caseless</option>
				<option>token-numeric</option>
				<option>custom</option>
			</select>
		</td>
		</tr>
		<tr>
		<td class="info">
			<b>time_limit</b>	
		</td>
		<td>
			<input id="time_limit" type="text">
		</td>
		<td class="info">
			<b>memory limit</b>
		</td>
		<td>
			<input id="memory_limit" type="text">
		</td>
		</tr>
		<tr>
		<td class="info">
			<b>source</b>
			
		</td>
		<td>
			<input id="source" type="text">
		</td>
		<td class="info">
			<b>zip</b>
		</td>
		<td>
			<input name="problem_contents" type="file" />
		</td>
		</tr>
		<tr>
		<td class="info">
		</td>
		<td>
		</td>
		<td class="info">
		</td>
		<td>
			<div class="POS Boton" onClick="sendProb()">Enviar problema</div>
		</td>
		</tr>
	</table>
	<input id="order" type="hidden" value="normal">
</form>
	</div>
</div>

<div class="post showable_on_error">
	<div class="copy">
		UPS error
	</div>
</div>

<style>
.showable_on_error{
	display:none;
}

div.problem-row div {
	float:left
}


div.problem-row div.ptitle {
	color: blue
}
</style>

{include file='footer.tpl'}
