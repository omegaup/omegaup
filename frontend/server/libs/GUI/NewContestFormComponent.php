<?php


class NewContestFormComponent implements GuiComponent{

	public function renderCmp(){
		?>
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
			<form method='POST' action=''>
				<tr>
					<td class="info" >
						<b>Scoreboard</b>
						<p >Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard ser&aacute; visible</p>
					</td>
					<td >
						<input id='scoreboard' name='scoreboard' value='' type='text'>
					</td>
					
					<td class="info">
						<b>Publico</b>
						<p></p>
					</td>
					<td >
						<select id='public'>
							<option value='Si'>Si</option>
							<option value='No'>No</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="info">
						<b>Inicio</b>
						<p></p>
					</td>
					<td  >
						<input id='start_time' name='start_time' value='' type='text'>
					</td>
					<td class="info">
						<b>Submissions Gap</b>
						<p>
						Tiempo m&iacute;nimo en segundos que debe de esperar un usuario despues de realizar un env&iacute;o para hacer otro
						</p>
					</td>
					<td >
						<input id='submissions_gap' name='submissions_gap' value='' type='text'>
					</td>
				</tr>
				<tr>
					<td class="info">
						<b>Window Length</b>
						<p>
						Indica el tiempo que tiene el usuario para env&iacute;ar soluci&oacute;n, si es NULL entonces ser&aacute; durante todo el tiempo del concurso
						</p>
					</td>
					<td >
						<input id='window_length' name='window_length' value='' type='text'>
					</td>
					<td class="info">
						<b>Title</b>
						<p>
						El titulo que aparecera en cada concurso</p>
					</td>
					<td >
						<input id='title' name='title' value='' type='text'>
					</td>
				</tr>
				<tr>
					<td class="info">
						<b>Penalty Time Start</b>
						<p>
						 Indica el momento cuando se inicia a contar el tiempo: cuando inicia el concurso o cuando se abre el problema
						</p>
					</td>
					<td >
						<select id='penalty_time_start'>
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
						<input id='penalty' name='penalty' value='' type='text'>
					</td>
				</tr>
				<tr>
					<td class="info">
						<b>Description</b>
						<p></p>
					</td>
					<td>
						<textarea id='description' name='description' ></textarea>
					</td>
					<td class="info">
						<b>Director</b>
						<p></p>
					</td>
					<td>
						<input id='director_id' name='director_id' value='' type='text'>
					</td>
				</tr>
				<tr>
					<td class="info">
						<b>Feedback</b>
						<p></p>
					</td>
					<td>
						<select id='feedback'>
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
						<select name="partial_score" id="partial_score">
							<option value="true">Si</option>
							<option value="false">No</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="info">
						<b>Finish Time</b>
						<p></p>
					</td>
					<td>
						<input id='finish_time' name='finish_time' value='' type='text'>
					</td>
					<td class="info">
						<b>Alias</b>
						<p>Almacenar&aacute; el token necesario para acceder al concurso</p>
						
					</td>
					<td>
						<input id='alias' name='alias' value='' type='text'>
					</td>
				</tr>
				<tr>
					<!--
					<select id='feedback'>
						<option value='sum'>Sum</option>
						<option value='max'>Max</option>
					</select>
					-->
				</tr>
				<tr>
					<td>
					</td>
					<td>
					</td>
					<td align='right'>
						<input value='Agendar concurso' type='submit'>
					</td>
				</tr>
			</form>
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