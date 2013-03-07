{include file='head.tpl'}
{include file='mainmenu.tpl'}

<div class="post">
	<div class="copy">
		<script type="text/javascript">
			var a = window.location.pathname.split("/");
			document.write('<a href="/arena/'+ a[a.length-1] +'"><div class="POS Boton" >Ir al concurso</div></a>');
		</script>
	</div>
</div>





<div class="post">
	<div class="copy">

		<progress></progress>

		<h2>Nuevo problema</h2>

		<form enctype="multipart/form-data" id="newProbForm">
		<table id="newprob" width="100%">
		<tr>
		<td class="info">
			<b>Title</b>
			<p>El titulo que tendrá el problema</p>
		</td>
		<td>
			<input id="title" type="text">
		</td>
		<td class="info">
			<b>Alias</b>
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
				<option value="1">Si</option>
				<option value="0">No</option>
			</select>
		</td>
		<td class="info">
			<b>Validator</b>
			
		</td>
		<td>
			<select id="validator">
				<option>remote</option>
				<option>literal</option>
				<option>token</option>
				<option>token-caseless</option>
				<option>token-numeric</option>
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
			<div class="POS Boton" onClick="sendProb()">Enviar Problema</div>
		</td>
		</tr>
	</table>
	<input id="order" type="hidden" value="normal">
</form>

		<script type="text/javascript">

			$(':file').change(function() {
			    var file = this.files[0];
			    name = file.name;
			    size = file.size;
			    type = file.type;
			});


			function progressHandlingFunction(e) {

			}

			function beforeSendHandler () {

			}

			function sendProb(){
			   var formData = new FormData($('#newProbForm')[0]);
			   formData.append("author_username", "alanboy");
			   formData.append("title", "tit" + parseInt( Math.random() * 100 ));
			   formData.append("alias", "ali" +  parseInt( Math.random() * 100 ));
			   formData.append("source", "asdf");
			   formData.append("public", "1");
			   formData.append("validator", "literal"); // //remote, literal, token, token-caseless, token-numeric
			   formData.append("time_limit", "2");
			   formData.append("memory_limit", "2");
			   formData.append("order", "normal");

			    $.ajax({
			        url: '/api/problem/create',  //server script to process data
			        type: 'POST',
			        xhr: function() {  // custom xhr
			            myXhr = $.ajaxSettings.xhr();
			            if(myXhr.upload){ // check if upload property exists
			                myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // for handling the progress of the upload
			            }
			            return myXhr;
			        },
			        //beforeSend: beforeSendHandler,
			        //success: completeHandler,
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
	</div>
</div>





<div class="post">
	<div class="copy wait_for_ajax" id="contest_details" >
		<table id="main" width="100%">
			<tr>
			<!-- ----------------------------------------- -->
				<td class="info">
					<b>Title</b>
					<p>
					 El titulo que tendrá el concurso
								</p>
							</td>
							<td>
								<input id='title' name='title' value='' type='text'>
							</td>
							<td class="info">
								<b>Alias</b>
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
								<b>Inicio</b>
								<p>
									La fecha (en hora local) en la que inicia el concurso
								</p>
							</td>
							<td>
								<input id='start_time' name='start_time' value='1359702610' type='text'>
							</td>
							<td class="info">
								<b>Fin</b>
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
								<b>Window Length</b>
								<p>
									Indica el tiempo que tiene el usuario para env&iacute;ar soluci&oacute;n, si es NULL entonces ser&aacute; durante todo el tiempo del concurso.
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
								<b>Scoreboard</b>
								<p>
									Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard ser&aacute; visible
								</p>
							</td>
							<td>
								<input id='scoreboard' name='scoreboard' value='100' type='text'>
							</td>
							<td class="info">
								<b>Submissions Gap</b>
								<p>
									Tiempo m&iacute;nimo en minutos que debe de esperar un usuario despues de realizar un env&iacute;o para hacer otro.
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
								<b>Penalty Time Start</b>
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
								<b>Penalty</b>
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
								<b>Feedback</b>
								<p>
									Si al usuario se le entrega retroalimentación inmediata sobre su problema
								</p>
							</td>
							<td>
								<select name='feedback' id='feedback'>
									<option value='yes'>Si</option>
									<option value='no'>No</option>
									<option value='partial'>Parcial</option>
								</select>
							</td>
							<td class="info">
								<b>Partial Score</b>
								<p>
									 Verdadero si el usuario recibir&aacute; puntaje parcial para problemas no resueltos en todos los casos
								</p>
							</td>
							<td>
								<select name="partial_score" id="partial_score">
									<option value="0">No</option>
									<option value="1">Si</option>
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
									<option value='sum'>Sum</option>
									<option value='max'>Max</option>
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




<script>
	(function(){
		//Load Contest details
		var a = window.location.pathname.split("/");
		omega.getContest(a[a.length-1], function(data){
				var html = "";
				$("#contest_details").removeClass("wait_for_ajax").append(html);
				for(var i in data) {
					$("#main #" + i).val( data[i] )
				}

			})
	})();
</script>
{include file='footer.tpl'}
