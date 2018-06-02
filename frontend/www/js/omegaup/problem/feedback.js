import Vue from 'vue';
import problem_Feedback from '../components/problem/Feedback.vue';
import {OmegaUp} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  const histograms = JSON.parse(document.getElementById('histograms').innerText);
  console.log(histograms);
  let problemFeedback = new Vue({
    el: '#problem-feedback',
    render: function(createElement) {
      return createElement('omegaup-problem-feedback', {

      });
    },
    data: {
      qualityHistogram: histograms.quality_histogram,
      difficultyHistogram: histograms.difficultyHistogram,
    },
    components: {
      'omegaup-problem-feedback': problem_Feedback,
    }
  });
});