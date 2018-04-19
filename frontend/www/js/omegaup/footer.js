import footer from './components/Footer.vue';
import {OmegaUp} from './omegaup.js';
import Vue from 'vue';


OmegaUp.on('ready', function(){

let footer = new Vue({
	el: '#footer',

	render: function(createElement){
		return createElement('omegaup-footer', {});
	},

	components: {
		'omegaup-footer': footer,
	},
});

});