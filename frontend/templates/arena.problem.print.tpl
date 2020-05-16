<!doctype html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>omegaUp &mdash; {$title|htmlspecialchars}</title>
<script type="text/javascript" src="{version_hash src="/js/mathjax-config.js"}"></script>
<script type="text/javascript" src="/third_party/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
<script type="text/javascript" src="{version_hash src="/third_party/js/jquery-3.5.1.min.js"}"></script>

<link rel="stylesheet" href="{version_hash src="/third_party/css/reset.css"}" />
<link rel="stylesheet" href="{version_hash src="/css/common.css"}" />
<link rel="stylesheet" href="{version_hash src="/css/arena.css"}" />
<link rel="stylesheet" href="{version_hash src="/css/report.css"}" />
</head>
<body id="report">
	<script type="text/json" id="payload">{$payload|json_encode}</script>
	<div class="title">
		<h1 class="problem-title">{$title|htmlspecialchars}</h2>
		<table class="data">
			<tr>
				<td>{#wordsPoints#}</td>
				<td class="points">&mdash;</td>
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
	<div class="statement"></div>
	{js_include entrypoint="problem_print" runtime}
</body>
</html>
