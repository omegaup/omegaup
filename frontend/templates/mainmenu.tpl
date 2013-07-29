	<div id="title">
		<a href="index.php">
		<div style="margin-left: 15%;">
			<img src="/media/omegaup_curves.png" alt="OmegaUp">
		</div>
		</a>
	</div>
	<div id="content">
		<div class="post footer">
			<ul>
				{if $CURRENT_USER_IS_ADMIN eq '1'}
					<li><a href='/admin/'><b>Admin</b></a></li>
				{/if}				
				<li><a href='/arena'><b>{#frontPageArena#}</b></a></li>
				{if $LOGGED_IN eq '1'}
					<li><a href='/contests.php'><b>{#frontPageMyContests#}</b></a></li>
				{/if}
				<li><a href='/probs.php'>{#frontPageProblems#}</a></li>
				<li><a href='/rank.php'>{#frontPageRanking#}</a></li>
				<li><a href='/recent.php'>{#frontPageRecent#}</a></li>
				<li><a href='https://github.com/omegaup/omegaup/'>{#frontPageDevelopers#}</a></li>
				<li><a href='/help.php'>{#frontPageHelp#}</a></li>
				<li><a href='http://blog.omegaup.com/'>{#frontPageBlog#}</a></li>
				<li><a href='https://omegaup.com/preguntas/'>{#frontPageQuestions#}</a></li>
			</ul>
		</div>
		{if $ERROR_TO_USER eq 'USER_OR_PASSWORD_WRONG'} 
		<div class="post footer">
			<div class="copy error">
				Your credentials are wrong
			</div>
		</div>
		{/if} 
		{if $ERROR_TO_USER eq 'EMAIL_NOT_VERIFIED'} 
		<div class="post footer">
			<div class="copy error">
				Your email is not verified yet. Please check your e-mail.
			</div>
		</div>
		{/if} 
