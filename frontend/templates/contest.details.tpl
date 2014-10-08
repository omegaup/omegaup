{assign var="htmlTitle" value="{#omegaupTitleContestDetails#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}


<script type="text/javascript">

	$(':file').change(function() {
		var file = this.files[0];
		name = file.name;
		size = file.size;
		type = file.type;
	});


	function progressHandlingFunction(e) {
	}
	

	var formData;

	function addProblemToContest(){

		var a = window.location.pathname.split("/");

		omegaup.addProblemToContest(
				a[a.length-1],
				"alanboy",
				$("#problem_upload_window #alias").val(),
				100,
				function(data){
					console.log("ya llegue de addproblem");
				}
		);
	}

	function sendProb(){
	   formData = new FormData($('#newProbForm')[0]);
	   formData.append("author_username", "alanboy");
	   formData.append("title", $("#problem_upload_window #title").val());
	   formData.append("alias", $("#problem_upload_window #alias").val());
	   formData.append("source",  $("#problem_upload_window #source").val());
	   formData.append("public", "1");
	   formData.append("validator", "token"); // token, token-caseless, token-numeric, custom
	   formData.append("time_limit",  $("#problem_upload_window #time_limit").val());
	   formData.append("memory_limit", $("#problem_upload_window #memory_limit").val());
	   formData.append("order", "normal");
		console.log (formData);
		$.ajax({
			url: '/api/problem/create',
			type: 'POST',
			xhr: function() {  // custom xhr
				myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){ // check if upload property exists
					myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // for handling the progress of the upload
				}
				return myXhr;
			},
			beforeSend: function(){
				console.log("voy a enviar");
			},
			success: function (){
				console.log("ya llegue");
					addProblemToContest();
			},
			//error: errorHandler,
			// Form data
			data: formData,
			//Options to tell JQuery not to process data or worry about content-type
			cache: false,
			contentType: false,
			processData: false
		});

	}
</script>






<!--
<div class="post hiddeable_on_error">
	<div class="copy">
		<script type="text/javascript">
			var a = window.location.pathname.split("/");
			document.write('<a href="/arena/'+ a[a.length-1] +'"><div class="POS Boton" >{#ContestDetailsGoToContest#}</div></a>');
		</script>
		<div class="POS Boton">{#wordsAddProblem#}</div>
	</div>
</div>
-->






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
								<b>{#contestDetailsPenaltyTimeStart#}</b>
								<p>
									 Indica el momento cuando se inicia a contar el tiempo: cuando inicia el concurso o cuando se abre el problema
								</p>
							</td>
							<td>
								<select name='penalty_time_start' id='penalty_time_start'>
									<option value='none'>none</option>
									<option value='problem'>problem</option>
									<option value='contest'>contest</option>
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


<script>
	function rendeProblemRow(title, alias, time_limit, memory_limit){
		var html = "";
		html = "<div class='problem-row'>"
				+ "<div class='ptitle'>" + title + "</div>"
				+ "<div class='alias'>" + alias + "</div>"
				+ "<div class='time_limit'>" + time_limit + "</div>"
				+ "<div class='memory_limit'>" + memory_limit + "</div>"
			+ "</div>";
		return html;
	}

	(function(){
		//Load Contest details
		var a = window.location.pathname.split("/");
		omegaup.getContest(a[a.length-1], function(data){
			console.log(data);
			if( data.status == "error" ){
					switch(data.errorcode){
						case 403:
								$(".hiddeable_on_error").hide();
								$(".showable_on_error").show();
						break;
						default:
					}
			}else{
					var html = "";
					$("#contest_details").removeClass("wait_for_ajax").append(html);
					for(var i in data) {
						$("#main #" + i).val(data[i])
					}

					$("#problem_details").removeClass("wait_for_ajax");
					for(var i in data.problems){
						console.log("agregando problema", data.problems[i]);
						$("#problem_details").append(rendeProblemRow( 
														data.problems[i].title,
														data.problems[i].alias,
														data.problems[i].time_limit,
														data.problems[i].memory_limit
													));
					}

			}
		});
	})();
</script>
{include file='footer.tpl'}
