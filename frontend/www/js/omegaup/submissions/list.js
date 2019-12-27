import Vue from 'vue';
import { API, OmegaUp, UI } from '../omegaup.js';
import submissions_List from '../components/submissions/List.vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(
    document.getElementById('submissions-payload').innerText,
  );
  let submissionsList = new Vue({
    el: '#omegaup-submissions-list',
    render: function(createElement) {
      return createElement('omegaup-submissions-list', {
        props: {
          page: this.page,
          length: this.length,
          includeUser: this.includeUser,
          submissions: this.submissions,
          totalRows: this.totalRows,
        },
      });
    },
    data: {
      page: payload.page,
      length: payload.length,
      includeUser: payload.includeUser,
      submissions: [],
      totalRows: 0,
    },
    components: {
      'omegaup-submissions-list': submissions_List,
    },
  });

  API.Submission.latestSubmissions({
    offset: submissionsList.page,
    rowcount: submissionsList.length,
  })
    .then(data => {
      console.log(data);
      submissionsList.totalRows = data.totalRows;
      submissionsList.submissions = data.submissions;
    })
    .catch(UI.apiError);
});
