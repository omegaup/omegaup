import {OmegaUp, T, API} from '../omegaup.js';
import problem_Solution from '../components/problem/Solution.vue';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  console.log(payload);
  let problemSolution = new Vue({
    el: '#problem-solution',
    render: function(createElement) {
      return createElement('omegaup-problem-solution', {
        props: {
        },
      });
    },
    data: {
    },
    components: {
      'omegaup-problem-solution': problem_Solution,
    }
  });
});