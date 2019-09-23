<div id="header" class="navbar navbar-default navbar-fixed-top" role="navigation">
  <div class="container navbar-inner">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#collapsible-navbar" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/">
        <img src="/media/omegaup_curves.png" alt="omegaUp" />
        {if $smarty.const.OMEGAUP_LOCKDOWN}
          <img title="lockdown" alt="lockdown" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAA6UlEQVQ4jd2TMYoCMRiFv5HBwnJBsFqEiGxtISps6RGmFD2CZRr7aQSPIFjmCGsnrFYeQJjGytJKRERsfp2QmahY+iDk5c97L/wJCchBFCclYAD8SmkBTI1WB1cb5Ji/gT+g7mxtgK7RausNiOIEYAm0pHSWOZR5BbSNVndPwTmlaZnnQFnGXGot0XgDfiw+NlrtjVZ7YOzRZAJCix893NZkAi4eYejRpJcYxckQ6AENKf0DO+EVoCN8DcyMVhM3eQR8WesO+WgAVWDituC28wiFDHkXHxBgv0IfKL7oO+UF1Ei/7zMsbuQKTFoqpb8KS2AAAAAASUVORK5CYII=" />
        {/if}
      </a>
    </div>
    <div class="navbar-collapse collapse" id="collapsible-navbar" aria-expanded="false" >
      <ul class="nav navbar-nav">
      {if !$smarty.const.OMEGAUP_LOCKDOWN && !(isset($inContest) && $inContest)}
          <li id="nav-arena"{if isset($navbarSection) && $navbarSection === "arena"} class="active"{/if}><a href="/arena/">{#navArena#}</a></li>
          {if $LOGGED_IN eq '1'}
            <li class="dropdown {if isset($navbarSection) && $navbarSection === 'contests'} active{/if}" id="nav-contests">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span>{#wordsContests#}</span><span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="/contest/new/">{#contestsCreateNew#}</a></li>
                <li><a href="/contest/mine/">{#navMyContests#}</a></li>
                <li><a href="/group/">{#navMyGroups#}</a></li>
                <li><a href="/scoreboardmerge.php">{#contestsJoinScoreboards#}</a></li>
              </ul>
            </li>
            <li class="dropdown {if isset($navbarSection) && $navbarSection === "problems"} active{/if}" id="nav-problems">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span>{#wordsProblems#}</span><span class="caret"></span></a>
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
            <li id="nav-problems"{if isset($navbarSection) && $navbarSection === "problems"} class="active"{/if}><a href="/problem/">{#wordsProblems#}</a></li>
          {/if} {* LOGGED_IN *}
          <li id="nav-rank"{if isset($navbarSection) && $navbarSection === "rank"} class="active"{/if}><a href="/rank/">{#navRanking#}</a></li>
          <li id="nav-schools"{if isset($navbarSection) && $navbarSection === "schools"} class="active"{/if}><a href="/schools/">{#navSchools#}</a></li>
          <li><a href="http://blog.omegaup.com/">{#navBlog#}</a></li>
          <li><a href="https://omegaup.com/preguntas/">{#navQuestions#}</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
          {if $LOGGED_IN eq '1'}
            {if isset($inContest) && $inContest}
              {include file='common.navbar.notifications.tpl'}
            {/if}
            <li id="notifications-list"></li>
            <li id="nav-user" class="dropdown{if isset($navbarSection) && $navbarSection === "users"} active{/if}">
              <a href="#" class="dropdown-toggle" id="user-dropdown" data-toggle="dropdown">
                {$CURRENT_USER_GRAVATAR_URL_51}
                <span class="username" title="{$CURRENT_USER_USERNAME}">{$CURRENT_USER_USERNAME}</span>
                {if $CURRENT_USER_IS_ADMIN eq '1'}
                  <span class="grader-count badge">…</span>
                {/if}
                <span class="caret"></span>
              </a>
              <ul class="dropdown-menu">
                <li><a href="/profile/"><span class="glyphicon glyphicon-user"></span> {#navViewProfile#}</a></li>
                <li><a href="/logout/"><span class="glyphicon glyphicon-log-out"></span> {#navLogOut#}</a></li>
                {if $CURRENT_USER_IS_ADMIN eq '1'}
                  <hr class="dropdown-separator">
                  <li class="grader-submissions"><a class="grader-submissions-link" href="/arena/admin/">{#wordsLatestSubmissions#}</a></li>
                  <li class="grader grader-status"></li>
                  <li class="grader grader-broadcaster-sockets"></li>
                  <li class="grader grader-embedded-runner"></li>
                  <li class="grader grader-queues"></li>
                {/if}
              </ul>
            </li>
          {else} {* LOGGED_IN *}
            <li><a href="/login/?redirect={$smarty.server.REQUEST_URI|escape:'url'}">{#navLogIn#}</a></li>
          {/if}
        {else} {* OMEGAUP_LOCKDOWN *}
          </ul>
          <ul class="nav navbar-nav navbar-right">
            {if $LOGGED_IN eq '1'}
              <li id="notifications-list"></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" id="user-dropdown" data-toggle="dropdown">{$CURRENT_USER_GRAVATAR_URL_51}<span class="username" title="{$CURRENT_USER_USERNAME}">{$CURRENT_USER_USERNAME}</span><span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="/logout/"><span class="glyphicon glyphicon-log-out"></span> {#navLogOut#}</a></li>
                </ul>
              </li>
            {else}
              <li><a href="/login/?redirect={$smarty.server.REQUEST_URI|escape:'url'}">{#navLogIn#}</a></li>
            {/if} {* LOGGED IN *}
        {/if} {* OMEGAUP_LOCKDOWN *}
      </ul>
    </div>
  {if $CURRENT_USER_IS_ADMIN eq '1'}
    <script type="text/javascript" src="{version_hash src="/js/common.navbar.grader_status.js"}"></script>
  {/if}
  {if $LOGGED_IN eq '1'}
    <script type="text/javascript" src="{version_hash src="/js/dist/notification_list.js"}"></script>
  {/if}
  </div>
</div>
