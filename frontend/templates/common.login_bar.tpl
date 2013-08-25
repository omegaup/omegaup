			<div id="header" class="navbar navbar-static-top" role="navigation">
				<div class="navbar-inner">
					<div class="container">
						<ul class="nav navbar-nav">
							{if $LOGGED_IN eq '1'}
							<li class="dropdown">
						    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="label">{$CURRENT_USER_GRAVATAR_URL_16}&nbsp;&nbsp; {$CURRENT_USER_USERNAME}<span class="caret"></span></a>
								<ul class="dropdown-menu">
								 <li><a href='/profile.php'>{#loginViewProfile#}</a></li>
								 <li><a href='/logout.php'>{#logOut#}</a></li>
								</ul>
							</li>	
							{else}
							<li><p class="navbar-text"><strong>{#pageTitle#}</strong></p></li>
							<li><a href='/login.php'>{#logIn#}</a></li>
							{/if}
						</ul>
						{if $CURRENT_USER_IS_ADMIN eq '1'}
						<ul class="nav navbar-nav navbar-right">
							<li id="grader-status" class="dropdown">
						    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="label"><img src="/media/waitcircle.gif" /></span> <span class="caret"></span></a>
								<ul class="dropdown-menu">
								</ul>
							</li>
						</ul>
						<script>
							function updateGraderStatus() {
								$("#grader-status a").css("background-color","#ffffff");
								$("#grader-status .label").html("<img src='/media/waitcircle.gif' />");
								omegaup.getGraderStats(function(stats){	
									if (stats && stats.status == "ok") {
										var graderInfo = stats.grader;

										if (graderInfo.status == "ok") {
											$("#grader-status a").css("background-color","#00aa33");
											html = "<li><a href=\"#\">Grader OK</a></li>";
											html += "<li><a href=\"#\">Embedded runner: " + graderInfo.embedded_runner + "</a></li>";
											html += "<li><a href=\"#\">Runners: " + graderInfo.runners + "</a></li>";
											html += "<li><a href=\"#\">Idle runners: " + graderInfo.runner_queue_length + "</a></li>";
										}
										else {
											$("#grader-status a").css("background-color","red");
											html = "<li><a href=\"#\">Grader DOWN</a></li>";
										}

										$("#grader-status .label").html(stats.pending_runs.length);
									} else {
										$("#grader-status a").css("background-color","red");
										html = "<li><a href=\"#\">Grader DOWN</a></li>";
										html += "<li><a href=\"#\">API api/grader/status call failed:";
										html += stats.error;
										html += "</a></li>";
										$("#grader-status .label").html('?');
									}
									$("#grader-status .dropdown-menu").html(html);
								});
							}

							updateGraderStatus();
							setInterval(updateGraderStatus,	30000);
						</script>
						{/if}
					</div>
				</div>
			</div>
