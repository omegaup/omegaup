{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#omegaupTitleProblemNew#}"}
{include file='problem.edit.form.tpl' new='true' tags=$SELECTED_TAGS|json_encode}
<span id="form-data" data-name="problems"></span>
<script src="{version_hash src="/js/alias.generate.js"}"></script>
{include file='footer.tpl'}
