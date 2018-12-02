import rank_table from './components/RankTable.vue';
import Vue from 'vue';
import {OmegaUp} from './omegaup.js';

OmegaUp.on('ready',function(){
	const payload = JSON.parse(document.getElementById('payload').innerText);
	let rankTable = new Vue({
		el: '#rank-table',
		render: function(createElement){
			return createElement('rankTable',{
				props:{
					page: this.page,
					length: this.length,
					is_index: this.is_index,
					availableFilters: this.availableFilters,
					filter: this.filter,
				}
			})
		},
		data: {
			page: payload.page,
			length: payload.length,
			is_index: payload.is_index,
			availableFilters: payload.availableFilters,
			filter: payload.filter,
		},
		components: {
			'rankTable': rank_table,
		},
	});
});
