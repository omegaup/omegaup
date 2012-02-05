<?php


class NewContestFormComponent implements GuiComponent{

	public function renderCmp(){
		?>
		<script type="text/javascript" charset="utf-8">
			$('#submit').submit(function(  ) {
				var start_time = new Date($("#_start_time").val());
				var finish_time = new Date($("#_finish_time").val());
				$.ajax({
					url: "/arena/contests/new",
					dataType: "json",
					type:"POST",
					data :{
						"title" 				: $("#_title").val(),
				        "description" 			: $("#_description").val(),
				        "start_time" 			: start_time.getTime(),
				        "finish_time" 			: finish_time.getTime(),
				        "window_length" 		: $("#_window_length").val(),
				        "public" 				: $("#_public").val(),
				        "alias" 				: $("#_alias").val(),
				        "scoreboard" 			: $("#_scoreboard").val(),
				        "points_decay_factor" 	: $("#_points_decay_factor").val(),
						"penalty_calc_policy"	: $("#_penalty_calc_policy").val(),
				        "partial_score" 		: $("#_partial_score").val(),
				        "submissions_gap"		: $("#_submissions_gap").val(),
				        "feedback" 				: $("#_feedback").val(),
				        "penalty" 				: $("#_penalty").val(),
				        "penalty_time_start" 	: $("#_penalty_time_start").val()
					},
					beforeSend: function( xhr ) {
					    $("#submit").hide(  );
					},
					success: function(a,b,c){
						$("#response").html("OK");
					},
					error:function(a,b,c){
						r = $.parseJSON( a.responseText );
						$("#submit").show(  );
						$("#response").html(r.error);
					}
					
				});
			});
		</script>
		<style>
			.new_contest p{
				color: gray !important;
				font-size: 10px !important;
			}

			.new_contest .info{
				text-align:right
			}
			.new_contest td{
				width: 25%;
				vertical-align:top;
			}
		</style>
		<hr>
		<h3>Nuevo concurso</h3>
		<div class="new_contest">
			<table width='100%' border=0>
				<tr><!-- ----------------------------------------- -->
					<td class="info" >
						<b>Scoreboard</b>
						<p>Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard ser&aacute; visible</p>
					</td>
					<td >
						<input id='_scoreboard' name='scoreboard' value='' type='text'>
					</td>
					
					<td class="info">
						<b>Publico</b>
						<p></p>
					</td>
					<td >
						<select id='_public'>
							<option value='1'>Si</option>
							<option value='0'>No</option>
						</select>
					</td>
				</tr><!-- ----------------------------------------- -->
				<tr><!-- ----------------------------------------- -->
					<td class="info">
						<b>Inicio</b>
						<p>La fecha (en hora local) en la que inicia el concurso</p>
					</td>
					<td  >
						<input id='_start_time' name='start_time' value='2012-01-01 00:00:00' type='text' >
					</td>
					<td class="info">
						<b>Submissions Gap</b>
						<p>Tiempo m&iacute;nimo en minutos que debe de esperar un usuario despues de realizar un env&iacute;o para hacer otro.</p>
					</td>
					<td >
						<input id='_submissions_gap' name='submissions_gap' value='' type='text'>
					</td>
				</tr><!-- ----------------------------------------- -->
				<tr><!-- ----------------------------------------- -->
					<td class="info">
						<b>Window Length</b>
						<p>Indica el tiempo que tiene el usuario para env&iacute;ar soluci&oacute;n, si es NULL entonces ser&aacute; durante todo el tiempo del concurso.</p>
					</td>
					<td >
						<input id='_window_length' name='window_length' value='' type='text'>
					</td>
					<td class="info">
						<b>Title</b>
						<p>
						El titulo que tendrá el concurso</p>
					</td>
					<td >
						<input id='_title' name='title' value='' type='text'>
					</td>
				</tr><!-- ----------------------------------------- -->
				<tr><!-- ----------------------------------------- -->
					<td class="info">
						<b>Penalty Time Start</b>
						<p>
						 Indica el momento cuando se inicia a contar el tiempo: cuando inicia el concurso o cuando se abre el problema
						</p>
					</td>
					<td >
						<select id='_penalty_time_start'>
							<option value='none'>none</option>
							<option value='problem'>problem</option>
							<option value='contest'>contest</option>
						</select>
					</td>
					<td class="info">
						<b>Penalty</b>
						<p>
						Entero indicando el n&oacute;mero de minutos con que se penaliza por recibir un no-accepted</p>
					</td>
					<td >
						<input id='_penalty' name='penalty' value='' type='text'>
					</td>
				</tr><!-- ----------------------------------------- -->
				<tr><!-- ----------------------------------------- -->
					<td class="info">
						<b>Description</b>
						<p></p>
					</td>
					<td>
						<textarea id='_description' name='description' ></textarea>
					</td>
					<td class="info">
						<b>Director</b>
						<p></p>
					</td>
					<td>
						<input id='_director_id' name='director_id' value='' type='text'>
					</td>
				</tr><!-- ----------------------------------------- -->
				<tr><!-- ----------------------------------------- -->
					<td class="info">
						<b>Feedback</b>
						<p>Si al usuario se le entrega retroalimentación inmediata sobre su problema</p>
					</td>
					<td>
						<select id='_feedback'>
							<option value='yes'>Si</option>
							<option value='no'>No</option>
							<option value='partial'>Parcial</option>
						</select>						
					</td>
					<td class="info">
						<b>Partial Score</b>
						<p>
						Verdadero si el usuario recibir&aacute; puntaje parcial para problemas no resueltos en todos los casos</p>
					</td>
					<td>
						<select name="partial_score" id="_partial_score">
							<option value="1">Si</option>
							<option value="0">No</option>
						</select>
					</td>
				</tr><!-- ----------------------------------------- -->
				<tr><!-- ----------------------------------------- -->
					<td class="info">
						<b>Finish Time</b>
						<p>La hora (en hora local) en la que termina el concurso.</p>
					</td>
					<td>
						<input id='_finish_time' name='finish_time' value='2012-01-02 00:00:00' type='text' >
					</td>
					<td class="info">
						<b>Alias</b>
						<p>Almacenar&aacute; el token necesario para acceder al concurso</p>
						
					</td>
					<td>
						<input id='_alias' name='alias' value='' type='text'>
					</td>
				</tr><!-- ----------------------------------------- -->
				<tr><!-- ----------------------------------------- -->
					<td class="info">
						<b>points_decay_factor</b>
						<p></p>
					</td>
					<td>
						<input id='_points_decay_factor' name='points_decay_factor' value='' type='text'>
					</td>
					<td class="info">
						<b>penalty_calc_policy</b>
						<p></p>
					</td>					
					<td>
						<select id='_penalty_calc_policy'>
							<option value='sum'>Sum</option>
							<option value='max'>Max</option>
						</select>
					</td>
				</tr><!-- ----------------------------------------- -->
				<tr><!-- ----------------------------------------- -->
					<td>
					</td>
					<td>
					</td>
					<td align='right'>
						<input value='Agendar concurso' type='button' id="submit">
						<div id="response">
							
						</div>
					</td>
				</tr><!-- ----------------------------------------- -->
			</table>
		</div>
		
		<?php
	}
	
}

/*
$new_contest = new DAOFormComponent( new Contests() );

$new_contest->hideField( array( "contest_id", "rerun_id" ) );

$new_contest->createComboBoxJoin( "public", "publico", array( "Si", "No" ) );

$new_contest->createComboBoxJoin( "penalty_time_start", "publico", array( "none", "problem", "contest" ) );

$new_contest->renameField(array(
		"director_id" => "user_id"
));

$new_contest->createComboBoxJoin( "user_id", "username", UsersDAO::getAll() );

$new_contest->addSubmit("Agendar concurso");

$page->addComponent( $new_contest );



$page->render();
*/
