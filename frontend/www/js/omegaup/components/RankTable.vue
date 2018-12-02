<template>
	<div class="panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<template v-if="!is_index">
			<h3 class="panel-title">
				{{ UI.formatString(T.rankRangeHeader, {lowCount:(page-1)*length+1,highCount:page*length}) }}
				</h3>
				
			<template v-if="page > 1">
				<a class="prev" v-bind:href="`/rank/?page=${page-1}`">{{ T.wordsPrevPage }}</a>
				<span class="delimiter"> | </span>
			</template>
			<a class="next" v-bind:href="`/rank/?page=${page+1}`">{{ T.wordsNextPage }}</a>
			<template v-if="Object.keys(availableFilters).length > 0 ">
		        <select class="filter">
		        	<option value="">{{ T.wordsSelectFilter }}</option>
		        	<option v-for="(item,key,index) in availableFilters" v-bind:value="index" v-bind:selected="filter_selected(key)">{{ item }}</option>
		        </select>
		   </template>
		</template>
		<template v-else>
		    <h3 class="panel-title">{{ UI.formatString(T.rankHeader,{count:length}) }}</h3>
		</template>
	</div>
	<div class="panel-body no-padding">
		<div class="table-responsive">
			<table class="table table-striped table-hover no-margin" id="rank-by-problems-solved" v-bind:data-length="length" v-bind:data-page="page" v-bind:data-filter="data_filter" v-bind:is-index="is_index">
				<thead>
					<tr>
						<th>#</th>
						<th colspan="2">{{ T.wordsUser }}</th>
						<th class="numericColumn">{{ T.rankScore }}</th>
						<th v-if="!is_index" class="numericColumn">{{ T.rankSolved }}</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<div class="container-fluid">
			<div class="col-xs-12 vertical-padding">
				<template v-if="is_index">
					<a href='/rank/'>{{ T.rankViewFull }}</a>
				</template>
				<template v-else>
					<template v-if="page > 1">
						<a class="prev" v-bind:href="prev_page_filter">{{ T.wordsPrevPage }}</a>
						<span class="delimiter"> | </span>
					</template>
						<a class="next" v-bind:href="next_page_filter">{{ T.wordsNextPage }}</a>
				</template>
				<br/>
			</div>
		</div>
	</div>
</div>
</template>

<script>
import {T} from '../omegaup.js';
import UI from '../ui.js';

export default {
	props:{
		page: Number,
		length: Number,
		is_index: Boolean,
		availableFilters: Object,
		filter: String,
	},
	data: function() {
		return { 
			T: T,
			UI: UI,
		}
	},
	methods:{
		filter_selected: function(key){
			console.log(key)
			if(this.filter == key)
				return "selected";
		}
	},
	computed: {
		next_page_filter: function(){
			if(this.filter != null )
				return "/rank/?page=" + (this.page + 1).toString() + "&filter=" + this.filter;
			else
				return "/rank/?page=" + (this.page + 1).toString();
		},
		prev_page_filter: function(){
			if(this.filter != null )
				return "/rank/?page=" + (this.page - 1).toString() + "&filter=" + this.filter;
			else
				return "/rank/?page=" + (this.page - 1).toString();
		},
		data_filter: function(){
			if(this.filter != null )
				return this.filter
		},
	}
};
</script>