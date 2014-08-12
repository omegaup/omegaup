<div class="pager-bar">
	<center>
		<ul class="pagination">
			{foreach from=$pager_links item=page}
				<li {if $page.class != ''}class="{$page.class}"{/if}>
					<a href="{$page.url}">{$page.label}</a>
				</li>
			{/foreach}
		</ul>
	</center>
</div>
