{include file='head.tpl' jsfile={version_hash src='/ux/contest.js'} bodyid='only-problem' inArena=true}
<script type="text/json" id="payload">{$payload|json_encode}</script>
  <ul class="tabs">
    <li><a href="#problems" class="active">{#wordsProblem#}</a></li>
    {if $payload['user']['logged_in']}
      <li><a href="#solution">{#wordsSolution#}</a></li>
    {/if}
    {if $problem_admin}
      <li><a href="#runs">{#wordsRuns#}</a></li>
      <li><a href="#clarifications">{#wordsClarifications#}<span id="clarifications-count"></span></a></li>
    {/if}
  </ul>
  <div id="problems" class="tab">
    <div id="problem" class="main">
      <h1 class="title">
        {if $visibility < 0}<span class="glyphicon glyphicon-ban-circle" title="{#wordsBannedProblem#}"></span>{/if}
        {if $visibility == 0}<span class="glyphicon glyphicon-eye-close" title="{#wordsPrivate#}"></span>{/if}
        {if $visibility >= 2}<img src="/media/quality-badge-sm.png" title="{#wordsHighQualityProblem#}"></img>{/if}
        {$title|escape}
        {if $problem_admin}
          (<a href="/problem/{$problem_alias}/edit/">{#wordsEdit#}</a>)
        {/if}
      </h1>
      <table class="data">
        <tr>
          <td>{#wordsPoints#}</td>
          <td class="points">{$points|escape}</td>
          <td>{#wordsMemoryLimit#}</td>
          <td class="memory_limit">{$memory_limit|escape}</td>
        </tr>
        <tr>
          <td>{#wordsTimeLimit#}</td>
          <td class="time_limit">{$time_limit|escape}</td>
          <td>{#wordsOverallWallTimeLimit#}</td>
          <td class="overall_wall_time_limit">{$overall_wall_time_limit|escape}</td>
        </tr>
        <tr>
          <td>{#problemEditFormInputLimit#}</td>
          <td class="input_limit">{$input_limit|escape}</td>
        </tr>
      </table>
      {if $karel_problem}
        <div class="karel-js-link">
          <a href="/karel.js/{if !empty($sample_input)}#mundo:{$sample_input|escape:url}{/if}" target="_blank">{#openInKarelJs#} <span class="glyphicon glyphicon-new-window"></span></a>
        </div>
      {/if}
      <div class="statement"></div>
      <hr />
      {if $source}
        <div class="source">{#wordsSource#}: <span class="source-data">{$source|escape}</span></div>
      {/if}
      {if $problemsetter}
        <div class="problemsetter">{#wordsProblemsetter#}: <a href="/profile/{$problemsetter.username}/">{$problemsetter.name|escape}</a></div>
        <div class="problem-creation-date"></div>
      {/if}
      {if !empty($ENABLED_EXPERIMENTS) && in_array('ephemeral', $ENABLED_EXPERIMENTS)}
        <iframe id="ephemeral-embedded-grader" src="/grader/ephemeral/?embedded"></iframe>
      {/if}
      <div>
        <script type="text/json" id="qualitynomination-reportproblem-payload">{$qualitynomination_reportproblem_payload|json_encode}</script>
        <div id="qualitynomination-demotionpopup"></div>
        <script type="text/javascript" src="{version_hash src="/js/dist/qualitynomination_demotionpopup.js"}"></script>
      </div>
      <div id="qualitynomination">
        <script type="text/json" id="quality-payload">{$quality_payload|json_encode}</script>
        <div id="qualitynomination-popup"></div>
        <script type="text/javascript" src="{version_hash src="/js/dist/qualitynomination_popup.js"}"></script>
      </div>
      {include file='arena.runs.tpl' show_submit=true show_details=true}
      {if isset($histograms)}
        <script type="text/json" id="histograms">{$histograms|json_encode}</script>
      {else}
        <script type="text/json" id="histograms">null</script>
      {/if}
      <div id="problem-feedback"></div>
      <script type="text/javascript" src="{version_hash src="/js/dist/problem_feedback.js"}"></script>
      <table class="best-solvers">
        <caption>{#wordsBestSolvers#}</caption>
        <thead>
          <tr>
            <th>{#wordsUser#}</th>
            <th>{#wordsLanguage#}</th>
            <th>{#wordsMemory#}</th>
            <th>{#wordsRuntime#}</th>
            <th>{#wordsTime#}</th>
          </tr>
        </thead>
        <tbody class="solver-list">
          <tr class="template">
            <td><a class="user"></a></td>
            <td class="language"></td>
            <td class="memory"></td>
            <td class="runtime"></td>
            <td class="time"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  {if $problem_admin}
    <div id="runs" class="tab">
      {include file='arena.runs.tpl' show_pager=true show_user=true show_rejudge=true show_details=true}
    </div>
  {/if}
  {include file='arena.clarification_list.tpl' contest=false}
  <div id="solution" class="tab">
    <div id="problem-solution"></div>
    <script type="text/javascript" src="{version_hash src="/js/dist/problem_solution.js"}"></script>
  </div>
</div>
<div id="overlay">
  {include file='arena.runsubmit.tpl'}
  <div id="run-details"></div>
</div>
<div id="footer"></div>
{include file='common.analytics.tpl'}
</body>
</html>
