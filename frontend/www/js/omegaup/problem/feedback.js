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
        props: {
          qualityHistogram: this.qualityHistogram,
          difficultyHistogram: this.difficultyHistogram,
        }
      });
    },
    data: {
      qualityHistogram: JSON.parse(histograms.quality_histogram),
      difficultyHistogram: JSON.parse(histograms.difficulty_histogram),
    },
    components: {
      'omegaup-problem-feedback': problem_Feedback,
    }
  });
});