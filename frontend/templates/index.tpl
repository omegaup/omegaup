{include file='head.tpl' htmlTitle="{#omegaupTitleIndex#}"}

<script src="https://www.google.com/jsapi?key=AIzaSyA5m1Nc8ws2BbmPRwKu5gFradvD_hgq6G0" type="text/javascript"></script>

<div class="row"> <!-- General information -->
	<div class="col-md-8"> <!--Carrusel -->
		<div class="panel panel-default">
			<div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
			<!-- Indicators -->
			<ol class="carousel-indicators">
				<li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
				<li data-target="#carousel-example-generic" data-slide-to="1"></li>
				<li data-target="#carousel-example-generic" data-slide-to="2"></li>
				<li data-target="#carousel-example-generic" data-slide-to="3"></li>
			</ol>

			<!-- Wrapper for slides -->
			<div class="carousel-inner" >
				<div class="item active">
					<img src="/media/carusel-mentores.jpg" alt="...">
					<div class="carousel-caption">
						<h3>Bienvenidos</h3>
						<p></p>
					</div>
				</div>
				<div class="item">
					<img src="/media/carusel-mentores.jpg" alt="...">
					<div class="carousel-caption">
						<h3>Mentors</h3>
					</div>
				</div>
				<div class="item">
					<img src="/media/carusel-mentores.jpg" alt="...">
					<div class="carousel-caption">
						<h3>Schools</h3>
						<p></p>
					</div>
				</div>
				<div class="item">
					<img src="/media/carusel-mentores.jpg" alt="...">
					<div class="carousel-caption">
						<h3>omegaUp org</h3>
						<p></p>
					</div>
				</div>
			</div>
			<!-- Controls -->
			<a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
			<span class="icon-prev"></span>
			</a>
			<a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
				<span class="icon-next"></span>
			</a>
			</div>
		</div>
	</div> <!-- Carrusel -->
	
	<div class="col-md-4">
			<div class="row">
				<div class="panel panel-info">
					<div class="panel-heading">{#index#}</div>
					<div class="panel-body">
						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="text-center" id="coder-of-the-month-img">
									<a href="/profile/{$coderOfTheMonthData.username|htmlspecialchars}">
									<!--	<img src="{$coderOfTheMonthData.gravatar_92}" /> -->
									<img src="/media/usuariosMes.png" alt="...">
									</a>
								</div>
							</div> 
						</div>
						<div class="col-md-6">
							<div class="panel panel-default">
				
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="panel panel-info">
					<div class="panel-heading">School of the month</div>
					<div class="panel-body">
						<div class="col-md-6">
							<div class="panel panel-default">
								<img src="/media/schoolMonth.png" alt="...">
							</div> 
						</div>
						<div class="col-md-6">
							<div class="panel panel-default">
								
							</div> 
						</div>
					</div>
				</div>
			</div>
	</div> 
</div>  <!-- General information -->

<div class="row"> <!-- Educational series -->
	<h3>Mejora tus habilidades de programación con nuestros cursos:</h3>
	<div class="col-md-4">
		<div class="panel panel-success">
			<div class="panel-heading">omegaUp-101</div>
			<div class="panel-body">
				<span>Breve descripción</span>
				<div><a href="https://omegaup.com/course/omegaup-101/">Ir al curso</a></div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="panel panel-success">
			<div class="panel-heading">Programación competitiva</div>
			<div class="panel-body">
				<span>Breve descripción</span>
				<div><a href="https://omegaup.com/course/omegaup-101/">Ir al curso</a></div>
			</div>
		</div> 
	</div>
	<div class="col-md-4">
		<div class="panel panel panel-success">
			<div class="panel-heading">Aprende Karel</div>
			<div class="panel-body">
				<span>Breve descripción</span>
				<div><a href="https://omegaup.com/course/omegaup-101/">Ir al curso</a></div>
			</div>
		</div> 
	</div>
</div>  <!-- Educational series -->

<div class="row"> <!-- Top users -->
	<div class="col-md-6">
		<div class="comentario">
			<span>El ranking colocará a la escuela con mayor número de usuarios activos en el primer lugar
		 y el número de problemas distintos resueltos se usará como criterio de desempate</span><span>Participa</span>
		 </div>
	</div>
	<div class="col-md-6"> 
		<div class="panel panel-default">
			{include file='rank.table.tpl' length=5 is_index=true}
		</div>
	</div>
</div>  <!-- Top users -->

<div class="row"> <!-- Top schools -->
	<div class="col-md-6"> 
		<div class="panel panel-default">
			<script type="text/json" id="schools-rank-payload">{$schoolRankPayload|json_encode}</script>
			<script type="text/javascript" src="{version_hash src="/js/dist/schools_rank.js"}"></script>
			<div id="omegaup-schools-rank"></div>
			<div class="container-fluid">
				<div class="col-xs-12 vertical-padding">
					<a href="/schoolsrank/">{#rankViewFull#}</a>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="comentario">
		<span>El ranking colocará a la escuela con mayor número de usuarios activos en el primer lugar
		 y el número de problemas distintos resueltos se usará como criterio de desempate</span>
		 </div>
	</div>
</div>  <!-- Top schools -->

<script type="text/javascript" src="{version_hash src="/js/index.js"}"></script>

<hr style="width: 100%; color: black; height: 1px; background-color:black;" />

{include file='footer.tpl'}

</div>
	


