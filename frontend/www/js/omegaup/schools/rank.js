import schools_Rank from '../components/schools/Rank.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
	let payload = JSON.parse(document.getElementById('schools-rank-payload').innerText);
	let schoolsRank = new Vue({
		el: '#schools-rank',
		render: function(createElement) {
			return createElement('omegaup-schools-rank', {
				props: {rank: this.rank},
			});
		},
		data: {
			rank: payload,
		},
		components: {
 	     'omegaup-schools-rank': schools_Rank,
	    },
	});
});

$(document).on('mouseenter', ".school-name-rank", function () {
 var $this = $(this);
 if (this.offsetWidth < this.scrollWidth && !$this.attr('title')) {
     $this.tooltip({
         title: $this.text(),
         placement: "bottom"
     });
     $this.tooltip('show');
 }
});