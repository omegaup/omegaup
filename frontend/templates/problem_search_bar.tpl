<div class="search-bar">
	<form action="/problem/list" method="GET">
		<div class="form-inline">
			<div class="form-group">
				<label class="control-label" for="order_by">{#wordsOrderBy#}</label>
				<select class="form-control" id="problem-search-order" name="order_by">
					<option {if $ORDER_BY == 'title'}		selected="selected"{/if}	value="title">{#wordsTitle#}</option>
					<option {if $ORDER_BY == 'submissions'}	selected="selected"{/if}	value="submissions">{#wordsRuns#}</option>
					<option {if $ORDER_BY == 'accepted'}	selected="selected"{/if}	value="accepted">{#wordsAccepted#}</option>
					<option {if $ORDER_BY == 'ratio'}		selected="selected"{/if}	value="ratio">{#wordsRatio#}</option>
					<option {if $ORDER_BY == 'points'}		selected="selected"{/if}	value="points">{#wordsPointsForRank#}</option>
					<option {if $ORDER_BY == 'score'}		selected="selected"{/if}	value="score">{#wordsMyScore#}</option>
				</select>
			</div>

			<div class="form-group">
				<label class="control-label" for="mode">{#wordsMode#}</label>
				<select class="form-control" id="problem-search-mode" name="mode">
					<option {if $MODE == 'asc'}	selected="selected"{/if}	value="asc">{#wordsModeAsc#}</option>
					<option {if $MODE == 'desc'}selected="selected"{/if}	value="desc">{#wordsModeDesc#}</option>
				</select>
			</div>

			<input class="btn btn-primary btn-lg active" type="submit" value="{#wordsSearch#}" id="problem-search-button"/>
		</div>
	</form>
</div>
