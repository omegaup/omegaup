{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleProblemNew#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

{include file='problem.edit.form.tpl' new='true'}
<span id="form-data" data-name="problems"></span>
<script src="{version_hash src="/js/alias.generate.js"}"></script>
{include file='footer.tpl'}
