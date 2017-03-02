<!doctype html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>omegaUp &mdash; {$contestName}</title>
<script type="text/javascript" src="{version_hash src="/js/mathjax-config.js"}"></script>
<script type="text/javascript" src="/third_party/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>

<link rel="stylesheet" href="/css/reset.css" />
<link rel="stylesheet" href="/css/common.css" />
<link rel="stylesheet" href="{version_hash src="/ux/arena.css"}" />
<link rel="stylesheet" href="/css/report.css" />
</head>
<body id="report">
{foreach name=outer item=problem from=$problems}
	<div class="title">
		<h1 class="problem-title">{$problem.letter}. {$problem.title}</h2>
		<table class="data">
			<tr>
				<td>{#wordsPoints#}</td>
				<td class="points">{$problem.points}</div>
				<td>{#arenaCommonMemoryLimit#}</td>
				<td class="memory_limit">{$problem.memory_limit / 1024} MB</td>
			</tr>
			<tr>
				<td>{#arenaCommonTimeLimit#}</td>
				<td class="time_limit">{$problem.time_limit / 1000} s</td>
				<td>{#arenaCommonOverallWallTimeLimit#}</td>
				<td class="time_limit">{$problem.overall_wall_time_limit / 1000} s</td>
			</tr>
		</table>
	</div>
	<div class="statement">
		{$problem.statement}
	</div>
	<hr />
	<div class="page-break"></div>
{/foreach}
</body>
</html>
