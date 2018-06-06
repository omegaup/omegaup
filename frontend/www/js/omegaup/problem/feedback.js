import Vue from 'vue';
import problem_Feedback from '../components/problem/Feedback.vue';
import {OmegaUp} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let problemFeedback = new Vue({
    el: '#problem-feedback',
    render: function(createElement) {
      return createElement('omegaup-problem-feedback', {
        props: {
          qualityHistogram: this.qualityHistogram,
          difficultyHistogram: this.difficultyHistogram,
          qualityScore: this.quality,
          difficultyScore: this.difficulty,
        }
      });
    },
    data: {
      qualityHistogram: JSON.parse(payload.histogram.quality_histogram),
      difficultyHistogram: JSON.parse(payload.histogram.difficulty_histogram),
      quality: payload.histogram.quality,
      difficulty: payload.histogram.difficulty,
    },
    components: {
      'omegaup-problem-feedback': problem_Feedback,
    }
  });
});
