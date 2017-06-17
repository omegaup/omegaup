			<div id="header" class="navbar navbar-default navbar-fixed-top" role="navigation">
				<div class="container navbar-inner">
					<div class="navbar-header">
						<a class="navbar-brand" href="/">
							<img src="/media/omegaup_curves.png" alt="omegaUp" />
							{if $smarty.const.OMEGAUP_LOCKDOWN}
							<img title="lockdown" alt="lockdown" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAA6UlEQVQ4jd2TMYoCMRiFv5HBwnJBsFqEiGxtISps6RGmFD2CZRr7aQSPIFjmCGsnrFYeQJjGytJKRERsfp2QmahY+iDk5c97L/wJCchBFCclYAD8SmkBTI1WB1cb5Ji/gT+g7mxtgK7RausNiOIEYAm0pHSWOZR5BbSNVndPwTmlaZnnQFnGXGot0XgDfiw+NlrtjVZ7YOzRZAJCix893NZkAi4eYejRpJcYxckQ6AENKf0DO+EVoCN8DcyMVhM3eQR8WesO+WgAVWDituC28wiFDHkXHxBgv0IfKL7oO+UF1Ei/7zMsbuQKTFoqpb8KS2AAAAAASUVORK5CYII=" />
							{/if}
						</a>
					</div>
					<ul class="nav navbar-nav">
						{if !$smarty.const.OMEGAUP_LOCKDOWN && !(isset($inContest) && $inContest)}
						<li id="nav-arena"{if isset($navbarSection) && $navbarSection == "arena"} class="active"{/if}><a href='/arena/'>{#navArena#}</a></li>
						{if $LOGGED_IN eq '1'}
							<li id="nav-contests"{if isset($navbarSection) && $navbarSection == 'contests'} class="active"{/if}>
								<a href='#' class="dropdown-toggle" data-toggle="dropdown"><span>{#wordsContests#}</span><span class="caret"></span></a>
								<ul class="dropdown-menu">
									<li><a href="/contest/new/">{#contestsCreateNew#}</a></li>
									<li><a href="/contest/mine/">{#navMyContests#}</a></li>
									<li><a href="/group/">{#navMyGroups#}</a></li>
									<li><a href="/scoreboardmerge.php">{#contestsJoinScoreboards#}</a></li>
								</ul>
							</li>
							<li id="nav-problems"{if isset($navbarSection) && $navbarSection == "problems"} class="active"{/if}>
								<a href='#' class="dropdown-toggle" data-toggle="dropdown"><span>{#wordsProblems#}</span><span class="caret"></span></a>
								<ul class="dropdown-menu">
									<li><a href="/problem/new/">{#myproblemsListCreateProblem#}</a></li>
									<li><a href="/problem/mine/">{#navMyProblems#}</a></li>
									<li><a href="/problem/">{#wordsProblems#}</a></li>
									<li><a href="/nomination/mine/">{#navMyQualityNomination#}</a></li>
									{if $CURRENT_USER_IS_REVIEWER eq '1'}
									<li><a href="/nomination/">{#navQualityNominationQueue#}</a></li>
									{/if}
								</ul>
							</li>
						{else}
							<li id="nav-problems"{if isset($navbarSection) && $navbarSection == "problems"} class="active"{/if}><a href='/problem/'>{#wordsProblems#}</a></li>
						{/if}
						<li class="hidden-xs hidden-sm{if isset($navbarSection) && $navbarSection == "rank"} active{/if}" id="nav-rank"><a href='/rank/'>{#navRanking#}</a></li>
						{if !empty($ENABLED_EXPERIMENTS) && in_array('schools', $ENABLED_EXPERIMENTS)}
						<li class="hidden-xs hidden-sm{if isset($navbarSection) && $navbarSection == "schools"} active{/if}" id="nav-schools"><a href='/schools/'>{#navSchools#}</a></li>
						{/if}
						<li class="hidden-xs hidden-sm"><a href='http://blog.omegaup.com/'>{#navBlog#}</a></li>
						<li class="hidden-xs hidden-sm"><a href='https://omegaup.com/preguntas/'>{#navQuestions#}</a></li>
						<li id="nav-mas" class="hidden-md hidden-lg">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span>+</span><span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href='/rank/'>{#navRanking#}</a></li>
								<li><a href='http://blog.omegaup.com/'>{#navBlog#}</a></li>
								<li><a href='https://omegaup.com/preguntas/'>{#navQuestions#}</a></li>
							</ul>
						</li>
						{/if}
					</ul>

					<ul class="nav navbar-nav navbar-right">
						{if $LOGGED_IN eq '1'}
{if isset($inContest) && $inContest}
{include file='common.navbar.notifications.tpl'}
{/if}
							<li class="dropdown">
							<a href="#" class="dropdown-toggle" id="user-dropdown" data-toggle="dropdown">{$CURRENT_USER_GRAVATAR_URL_51}<span class="username" title="{$CURRENT_USER_USERNAME}">{$CURRENT_USER_USERNAME}</span><span class="caret"></span></a>
								<ul class="dropdown-menu">
								 <li><a href='/profile/'><span class="glyphicon glyphicon-user"></span> {#navViewProfile#}</a></li>
								 <li><a href='/logout/'><span class="glyphicon glyphicon-log-out"></span> {#navLogOut#}</a></li>
								</ul>
							</li>
						{else}
							<li><a href='/login/?redirect={$smarty.server.REQUEST_URI|escape:'url'}'>{#navLogIn#}</a></li>
						{/if}

						{if $CURRENT_USER_IS_ADMIN eq '1'}
							<li id="grader-status" class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span id="grader-count"><img src="/media/waitcircle.gif" /></span> <span class="caret"></span></a>
								<ul class="dropdown-menu">
								</ul>
							</li>
						{/if}
					</ul>
					{if $CURRENT_USER_IS_ADMIN eq '1'}
					<script type="text/javascript" src="{version_hash src="/js/common.navbar.grader_status.js"}"></script>
					{/if}
				</div>
			</div>
