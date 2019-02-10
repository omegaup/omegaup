<!DOCTYPE html>
<html lang="{#locale#}">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>omegaUp &mdash; {$contestName}</title>
    <script type="text/javascript" src="{version_hash src="/js/mathjax-config.js"}"></script>
    <script type="text/javascript" src="/third_party/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
    <script type="text/javascript" src="{version_hash src="/third_party/js/jquery-3.2.1.min.js"}"></script>
    <script type="text/javascript" src="{version_hash src="/third_party/js/jquery-migrate-3.0.1.min.js"}"></script>
    <script type="text/javascript" src="{version_hash src="/js/dist/commons.js"}"></script>
    <script type="text/javascript" src="{version_hash src="/js/dist/omegaup.js"}"></script>
    <script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Converter.js"}"></script>
    <script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Sanitizer.js"}"></script>

    <link rel="stylesheet" href="{version_hash src="/third_party/css/reset.css"}" />
    <link rel="stylesheet" href="{version_hash src="/css/common.css"}" />
    <link rel="stylesheet" href="{version_hash src="/ux/arena.css"}" />
    <link rel="stylesheet" href="{version_hash src="/css/report.css"}" />
  </head>
  <body id="report">
{foreach item=problem from=$problems}
    <div class="problem">
      <script type="text/json" class="payload">{$problem.payload|json_encode}</script>
      <div class="title">
        <h1 class="problem-title">{$problem.letter}. {$problem.title|htmlspecialchars}</h2>
        <table class="data">
          <tr>
            <td>{#wordsPoints#}</td>
            <td class="points">{$problem.points}</div>
            <td>{#arenaCommonMemoryLimit#}</td>
            <td class="memory_limit">{$problem.memory_limit / 1024} MiB</td>
          </tr>
          <tr>
            <td>{#arenaCommonTimeLimit#}</td>
            <td class="time_limit">{$problem.time_limit / 1000} s</td>
            <td>{#arenaCommonOverallWallTimeLimit#}</td>
            <td class="time_limit">{$problem.overall_wall_time_limit / 1000} s</td>
          </tr>
        </table>
      </div>
      <div class="statement"></div>
      <hr />
      <div class="page-break"></div>
    </div>
{/foreach}
  <script type="text/javascript" src="{version_hash src="/js/contest.print.js"}"></script>
  </body>
</html>
