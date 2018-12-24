import contest_report from '../components/contest/Report.vue';
import {OmegaUp} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready',function(){
	const payload = JSON.parse(document.getElementById('payload').innerText);

	let contestReport = new Vue({
		el: '#contest-report',
		render: function(createElement){
			return createElement('contestReport',{
				props: {
					contestReport: this.contestReport,
				}
			})
		},
		data: {
			contestReport: payload.contestReport,
		},
		components :{
			contestReport: contest_report,
		}
	})

});