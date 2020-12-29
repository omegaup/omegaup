{include file='head.tpl' bodyid='only-problem' inArena=true inline}
<script type="text/json" id="payload">{$payload|json_encode}</script>
{js_include entrypoint="arena_contest"}
  {if !empty($payload['languages'])}
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
  {/if}
  <div id="problems" class="tab">
    <div id="problem" class="main">
      <div id="problem-settings-summary"></div>
      <script type="text/json" id="settings-summary-payload">{$settings_summary_payload|json_encode}</script>
      {js_include entrypoint="problem_settings_summary"}
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
      {if $LOGGED_IN}
      <div>
        <div id="qualitynomination-qualityreview"></div>
        {js_include entrypoint="qualitynomination_qualityreview"}
      </div>
      <div>
        <script type="text/json" id="qualitynomination-reportproblem-payload">{$nomination_payload|json_encode}</script>
        <div id="qualitynomination-demotionpopup"></div>
        {js_include entrypoint="qualitynomination_demotionpopup"}
      </div>
      {/if}
      <div id="qualitynomination">
        <script type="text/json" id="quality-payload">{$quality_payload|json_encode}</script>
        <div id="qualitynomination-popup"></div>
        {js_include entrypoint="qualitynomination_popup"}
      </div>
      <table class="runs"></table>
      {if isset($histograms)}
        <script type="text/json" id="histograms">{$histograms|json_encode}</script>
      {else}
        <script type="text/json" id="histograms">null</script>
      {/if}
      <div id="problem-feedback"></div>
      {js_include entrypoint="problem_feedback"}
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
      <table class="runs"></table>
    </div>
  {/if}
  {include file='arena.clarification_list.tpl' contest=false inline}
  <div id="solution" class="tab">
    <div id="problem-solution"></div>
    {js_include entrypoint="problem_solution"}
  </div>
</div>
<div id="overlay">
  <div id="run-submit"></div>
  <div id="run-details"></div>
</div>
<div id="footer"></div>
{include file='common.analytics.tpl' inline}
</body>
</html>
