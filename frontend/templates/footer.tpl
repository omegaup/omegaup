		<hr>
		<!-- #content -->
	</div>

	<div id="common-footer"></div>
    <script type="text/json" id="payload">{$payload|json_encode}</script>
	{js_include entrypoint="common_footer"}
</div>

{if $OMEGAUP_GA_TRACK eq 1}
	<script type="text/javascript" src="{version_hash src="/js/analytics.js"}"></script>
{/if}
<!-- #root -->
</body>
</html>
