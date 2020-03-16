<div class="search-bar">
	<form action="/problem/" method="GET">
		<div class="form-inline">
			{if !empty($smarty.get.tag)}
			<div class="form-group">
				{foreach item=tag from=$smarty.get.tag}
					<input type="hidden" name="tag[]" value="{$tag|urlencode}" />
					<span class="tag">{$tag|escape}</span>
				{/foreach}
				<a class="remove-all-tags" href="/problem/"><span class="glyphicon glyphicon-remove"></span></a>
			</div>
			{/if}
			<div class="form-group">
				<input class="form-control" id="problem-search-box"
						type="text" name='query' autocomplete="off"
						{if $KEYWORD != ''} value="{$KEYWORD}"{/if}
						placeholder="{#wordsKeyword#}">
			</div>

			<div class="form-group">
				<label class="control-label" for="mode">{#wordsFilterByLanguage#}</label>
				<select class="form-control" id="problem-search-language" name="language">
					<option {if $LANGUAGE == null} selected="selected"{/if} value="all">{#wordsAll#}</option>
					<option {if $LANGUAGE == 'es'}	selected="selected"{/if} value="es">{#wordsSpanish#}</option>
					<option {if $LANGUAGE == 'en'}	selected="selected"{/if} value="en">{#wordsEnglish#}</option>
					<option {if $LANGUAGE == 'pt'}	selected="selected"{/if} value="pt">{#wordsPortuguese#}</option>
				</select>
			</div>

			<div class="form-group">
				<label class="control-label" for="order_by">{#wordsOrderBy#}</label>
				<select class="form-control" id="problem-search-order" name="order_by">
					<option {if $ORDER_BY == 'title'}		selected="selected"{/if}	value="title">{#wordsTitle#}</option>
					<option {if $ORDER_BY == 'quality'}	selected="selected"{/if}	value="quality">{#wordsQuality#}</option>
					<option {if $ORDER_BY == 'difficulty'}	selected="selected"{/if}	value="difficulty">{#wordsDifficulty#}</option>
					<option {if $ORDER_BY == 'submissions'}	selected="selected"{/if}	value="submissions">{#wordsRuns#}</option>
					<option {if $ORDER_BY == 'accepted'}	selected="selected"{/if}	value="accepted">{#wordsAccepted#}</option>
					<option {if $ORDER_BY == 'ratio'}		selected="selected"{/if}	value="ratio">{#wordsRatio#}</option>
					<option {if $ORDER_BY == 'points'}		selected="selected"{/if}	value="points">{#wordsPointsForRank#}</option>
					<option {if $ORDER_BY == 'score'}		selected="selected"{/if}	value="score">{#wordsMyScore#}</option>
					<option {if $ORDER_BY == 'creation_date'} selected="selected"{/if}	value="creation_date">{#codersOfTheMonthDate#}</option>
				</select>
			</div>

			<div class="form-group">
				<label class="control-label" for="mode">{#wordsMode#}</label>
				<select class="form-control" id="problem-search-mode" name="mode">
					<option {if $MODE == 'asc'}	selected="selected"{/if}	value="asc">{#wordsModeAsc#}</option>
					<option {if $MODE == 'desc'} selected="selected"{/if}	value="desc">{#wordsModeDesc#}</option>
				</select>
			</div>

			<input class="btn btn-primary btn-lg active" type="submit" value="{#wordsSearch#}" id="problem-search-button"/>
		</div>
	</form>
</div>
<script type="text/javascript" src="{version_hash src="/js/problem_search_bar.js"}" defer></script>