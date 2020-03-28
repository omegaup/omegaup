import course_Details from '../components/course/CourseDetails.vue';
import { OmegaUp } from '../omegaup';
import API from '../api.js';
import UI from '../ui.js';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  const courseDetails = new Vue({
    el: '#course-details',
    render: function(createElement) {
      return createElement('omegaup-course-details', {
        props: { course: this.course, progress: this.progress },
      });
    },
    data: { course: payload.details, progress: payload.progress },
    components: {
      'omegaup-course-details': course_Details,
    },
  });
});
