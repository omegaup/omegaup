			<div id="login_bar">
				{$CURRENT_USER_GRAVATAR_URL_16}
				{if $LOGGED_IN eq '1'}
					 <a href="/profile.php">{$CURRENT_USER_USERNAME}</a> <b><a href='/logout.php'>{#logOut#}</a></b>
				{else}
					{#pageTitle#} <b><a href='/login.php'>{#logIn#}</a>!</b>
				{/if}
			</div>
			{if $CURRENT_USER_IS_ADMIN eq '1'}
			<div id="status_bar" style="display: block; background-color: #00aa33;">
				<img src="/media/waitcircle.gif" />
			</div>
			<script>
				function updateGraderStatus() {
					$("div#status_bar").html("<img src='/media/waitcircle.gif' />");

					omegaup.getGraderStats(function(stats){
						if (stats.status == "ok") {

							$("div#status_bar").css("background-color","#00aa33");
							graderInfo = stats.grader;

							if (graderInfo.status == "ok") {
								html = "Grader OK | ";
								html += "<b>Embedded runner: </b>" + graderInfo.embedded_runner + " | ";
								html += "<b>Queue length: </b>" + graderInfo.runner_queue_length + " | ";
								html += "<b>Runners: </b>" + graderInfo.runners + " | ";
							}
							else {
								$("div#status_bar").css("background-color","red");
								html = "<b>Grader down D: </b>";
							}

							html += "<b>Pending runs: " + stats.pending_runs.length; + "</b>";
						}
						else {
							$("div#status_bar").css("background-color","red");
							html = "<b>Grader down D: API api/grader/status call failed:  </b>";
							html += stats.error;
						}

						$("div#status_bar").html(html);
					});
				}

				updateGraderStatus();
				setInterval(updateGraderStatus,	30000);
			</script>
			{/if}
