{include file='head.tpl' htmlTitle="{#omegaupTitleIndex#}" inline}

{if isset($coderOfTheMonthData)}
<script type="text/json" id="coder-of-the-month-payload">{$coderOfTheMonthData|json_encode}</script>
{else}
<script type="text/json" id="coder-of-the-month-payload">null</script>
{/if}
{if $LOGGED_IN eq '1'}
<script type="text/json" id="current-user-payload">{$currentUserInfo|json_encode}</script>
{else}
<script type="text/json" id="current-user-payload">null</script>
{/if}
{js_include entrypoint="coder_of_the_month_notice" async}
<div id="coder-of-the-month-notice"></div>

<div class="container-fluid">
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
				<p class="text-center top-margin">{#frontPageJoinCourse#}</p>
				<div class="text-center">
					<a href="/course/Curso-OMI" class="btn btn-primary btn-lg">{#frontPageJoinCourseButton#}</a>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				{include file='rank.table.tpl' rankTablePayload=$rankTablePayload inline}
			</div>

			{if isset($schoolRankPayload)}
			<div class="col-md-6">
				<div class="panel panel-default">
					<script type="text/json" id="schools-rank-payload">{$schoolRankPayload|json_encode}</script>
					{js_include entrypoint="schools_rank" async}
					<div id="omegaup-schools-rank"></div>
					<div class="container-fluid">
						<div class="col-xs-12 vertical-padding">
							<a href="/schoolsrank/">{#rankViewFull#}</a>
						</div>
					</div>
				</div>
			</div>
			{/if}

		</div>
	</div>

	<div class="col-md-4">
		{if $ENABLE_SOCIAL_MEDIA_RESOURCES}
		<div class="panel panel-default">
			<div class="panel-body">
				<!-- Facebook like button -->
				<iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fomegaup&width=137&layout=button_count&action=like&size=small&show_faces=false&share=true&height=46&appId" width="137" height="46" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allow="encrypted-media"></iframe>
				<br/>
				<!-- Twitter follow -->
				<a href="https://twitter.com/omegaup?ref_src=twsrc%5Etfw" class="twitter-follow-button" data-width="300px" data-height="20" data-show-screen-name="false" data-dnt="true" data-show-count="true">Follow @omegaup</a>
				</div>
		</div>
		{/if}

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
						{if isset($coderOfTheMonthData.state)} {$coderOfTheMonthData.state|htmlspecialchars}, {/if}{if $coderOfTheMonthData.country != 'xx'}{$coderOfTheMonthData.country|htmlspecialchars}{/if}
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

		<div class="panel panel-default">
			<div id="runs-chart"></div>
		</div>
	</div>
</div>
</div><!-- container -->

<script type="text/javascript" src="{version_hash src="/js/index.js"}" async></script>

{include file='footer.tpl' inline}
