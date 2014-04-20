<!doctype html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>omegaUp &mdash; {$contestName}</title>
{literal}
		<script type="text/javascript" src="https://c328740.ssl.cf1.rackcdn.com/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
		<script type="text/x-mathjax-config">MathJax.Hub.Config({tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}});</script>
{/literal}

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
				<td>{#wordsValidator#}</td>
				<td class="validator">{$problem.validator}</div>
			</tr>
			<tr>
				<td>{#arenaCommonTimeLimit#}</td>
				<td class="time_limit">{$problem.time_limit / 1000} s</td>
				<td>{#arenaCommonMemoryLimit#}</td>
				<td class="memory_limit">{$problem.memory_limit / 1024} MB</td>
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
