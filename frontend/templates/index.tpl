{include file='head.tpl' htmlTitle="{#omegaupTitleIndex#}"}

<script src="https://www.google.com/jsapi?key=AIzaSyA5m1Nc8ws2BbmPRwKu5gFradvD_hgq6G0" type="text/javascript"></script>

<script type="text/json" id="coder-of-the-month-payload">{$coderOfTheMonthData|json_encode}</script>
{if $LOGGED_IN eq '1'}
<script type="text/json" id="current-user-payload">{$currentUserInfo|json_encode}</script>
{else}
<script type="text/json" id="current-user-payload">null</script>
{/if}
<script type="text/javascript" src="{version_hash src="/js/dist/coder_of_the_month_notice.js"}"></script>
<div id="coder-of-the-month-notice"></div>

<div class="container-fluid">
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
					<img src="/media/carrusel-background.png" alt="...">
					<div class="carousel-caption">
						<h3>Bienvenidos</h3>
						<p></p>
					</div>
				</div>
				<div class="item">
					<img src="/media/carrusel-background.png" alt="...">
					<div class="carousel-caption">
						<h3>omegaUp mentors</h3>
						<p>Un programa para </p>
					</div>
				</div>
				<div class="item">
					<img src="/media/carrusel-background.png" alt="...">
					<div class="carousel-caption">
						<h3>Schools</h3>
						<p></p>
					</div>
				</div>
				<div class="item">
					<img src="/media/carrusel-background.png" alt="...">
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
		<div class="panel panel-default">
			<div class="row">
				<div class="col-md-6">
					<div class="panel panel-default">
						<div class="text-center" id="coder-of-the-month-img">
							<a href="/profile/{$coderOfTheMonthData.username|htmlspecialchars}">
								<img src="{$coderOfTheMonthData.gravatar_92}" />
							</a>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="panel panel-default">
						<h3 class="panel-title">{#index#}</h3>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="panel panel-default">
						<img />
					</div>
				</div>
				<div class="col-md-6">
					<div class="panel panel-default">
						<h3 class="panel-title">School of the month</h3>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>  <!-- General information -->

<div class="row"> <!-- Educational series -->
	<div class="col-md-4">
		<div class="panel panel-default">
			<div>
				<img />
			</div>
			<div>
				<span>Curso 1</span>
			</div>

		</div>
	</div>
	<div class="col-md-4">
		<div class="panel panel-default">
		</div>
	</div>
	<div class="col-md-4">
		<div class="panel panel-default">
		</div>
	</div>
</div>  <!-- Educational series -->

<div class="row"> <!-- Top users -->
	<div class="col-md-6">
		<img />
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
		<img />
	</div>
</div>  <!-- Top schools -->

</div>

<div>
		<!-- Facebook like button -->
	<iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fomegaup&amp;width&amp;layout=button_count&amp;action=like&amp;show_faces=false&amp;share=true&amp;height=21&amp;appId=197705690257857" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px;" allowTransparency="true"></iframe>
	<!-- Twitter follow -->
	<iframe allowtransparency="true" frameborder="0" scrolling="no"
		src="https://platform.twitter.com/widgets/follow_button.html?screen_name=omegaup"
		style="width:300px; height:20px;"></iframe>
</div>

</div><!-- container -->
<script type="text/javascript" src="{version_hash src="/js/index.js"}"></script>

{include file='footer.tpl'}
