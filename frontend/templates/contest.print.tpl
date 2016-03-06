<!doctype html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>omegaUp &mdash; {$contestName}</title>
<script type="text/javascript" src="/js/mathjax-config.js?ver=37494e"></script>
<script type="text/javascript" src="/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>

<link rel="stylesheet" href="/css/reset.css" />
<link rel="stylesheet" href="/css/common.css" />
<link rel="stylesheet" href="/ux/arena.css?t=2" />
<link rel="stylesheet" href="/css/report.css" />
</head>
<body id="report">
{foreach name=outer item=problem from=$problems}
	<div class="title">
		<h1 class="contest-title">{$problem.letter}. {$problem.title}</h1>
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
