<!doctype html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>omegaUp &mdash; {$title}</title>
<script type="text/javascript" src="/js/mathjax-config.js?ver=37494e"></script>
<script type="text/javascript" src="/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>

<link rel="stylesheet" href="/css/reset.css" />
<link rel="stylesheet" href="/css/common.css" />
<link rel="stylesheet" href="/ux/arena.css?t=2" />
<link rel="stylesheet" href="/css/report.css" />
</head>
<body id="report">
	<div class="title">
		<h1 class="problem-title">{$title}</h2>
		<table class="data">
			<tr>
				<td>{#wordsPoints#}</td>
				<td class="points">&mdash;</div>
				<td>{#arenaCommonMemoryLimit#}</td>
				<td class="memory_limit">{$memory_limit}</td>
			</tr>
			<tr>
				<td>{#arenaCommonTimeLimit#}</td>
				<td class="time_limit">{$time_limit}</td>
				<td>{#arenaCommonOverallWallTimeLimit#}</td>
				<td class="time_limit">{$overall_wall_time_limit}</td>
			</tr>
		</table>
	</div>
	<div class="statement">
		{$problem_statement}
	</div>
</body>
</html>
