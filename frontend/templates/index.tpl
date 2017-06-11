{include file='head.tpl' htmlTitle="{#omegaupTitleIndex#}"}

<script src="https://www.google.com/jsapi?key=AIzaSyA5m1Nc8ws2BbmPRwKu5gFradvD_hgq6G0" type="text/javascript"></script>
<div class="row">
	<div class="col-md-8">
		<div class="panel panel-default">
			<div class="jumbotron no-bottom-margin">
				<h1 class="text-center">{#frontPageWelcome#}</h1>
				<p class="top-margin">{#frontPageDescription#}</p>
				<div class="text-center">
					<a href="/contest/new/" class="btn btn-primary btn-lg" id="contest-create">{#frontPageCreateContestButton#}</a>
				</div>
				<p class="text-center top-margin">{#frontPageIntroduction#}</p>
				<div class="text-center">
					<a href="http://blog.omegaup.com/category/omegaup/omegaup-101/" class="btn btn-primary btn-lg">{#frontPageIntroductionButton#}</a>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-body">
				<!-- Facebook like button -->
				<iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fomegaup&amp;width&amp;layout=button_count&amp;action=like&amp;show_faces=false&amp;share=true&amp;height=21&amp;appId=197705690257857" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px;" allowTransparency="true"></iframe>
				<br/>
				<!-- Twitter follow -->
				<iframe allowtransparency="true" frameborder="0" scrolling="no"
					src="https://platform.twitter.com/widgets/follow_button.html?screen_name=omegaup"
					style="width:300px; height:20px;"></iframe>
			</div>
		</div>
		{if isset($coderOfTheMonthData)}
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">{#index#}</h3>
			</div>
			<div id="coder_of_the_month" class="panel-body">
				<div class="rss_element">
					<h4 class="text-center" id="coder-of-the-month-username"><a href="/profile/{$coderOfTheMonthData.username|htmlspecialchars}">{$coderOfTheMonthData.username|htmlspecialchars}</a><img src="/media/flags/{$coderOfTheMonthData.country_id|lower}.png" width="16" height="11" title="{$coderOfTheMonthData.country_id}"/></h4>
					<div class="text-center" id="coder-of-the-month-img"><a href="/profile/{$coderOfTheMonthData.username|htmlspecialchars}"><img src="{$coderOfTheMonthData.gravatar_92}"></a></div>
					<div id="coder-of-the-month-name">{$coderOfTheMonthData.name|htmlspecialchars}</div>
					<div id="coder-of-the-month-school">{$coderOfTheMonthData.school|htmlspecialchars}</div>
					<div id="coder-of-the-month-place">
						{if isset($coderOfTheMonthData.state)} {$coderOfTheMonthData.state|htmlspecialchars}, {/if}{$coderOfTheMonthData.country|htmlspecialchars}
					</div>
				</div>
			</div>
			<div class="panel-body">
				<a href="/coderofthemonth/">{#coderOfTheMonthFullList#}</a>
			</div>
		</div>
		{/if}

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">{#frontPageMaterial#}</h3>
			</div>
			<div id="recommended_material" class="panel-body">
				<a class="text-center center" href="https://omegaup.com/img/libropre3.pdf">Descarga en PDF aquí:
				<img class="center top-margin" src="https://omegaup.com/img/libroluis.gif" width="75%"/>
				</a>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">{#frontPageNextContests#}</h3>
			</div>
			<ul class="list-group" id="next-contests-list">
		    </ul>
		</div>

		{include file='rank.table.tpl' length=5 is_index=true}

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

		<div class="panel panel-default">
			<div id="runs-chart"></div>
		</div>
	</div>
</div>

<script type="text/javascript" src="{version_hash src="/js/index.js"}"></script>

{include file='footer.tpl'}
