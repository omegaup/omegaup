import schools_Rank from '../components/schools/Rank.vue';
import { API, UI, OmegaUp, T } from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(
    document.getElementById('school-rank-payload').innerText,
  );
  let schoolsRank = new Vue({
    el: '#omegaup-schools-rank',
    render: function(createElement) {
      return createElement('omegaup-schools-rank', {
        props: {
          page: this.page,
          length: this.length,
          isIndex: this.isIndex,
          rank: this.rank,
          totalRows: this.totalRows,
        },
      });
    },
    data: {
      page: payload.page,
      length: payload.length,
      isIndex: payload.isIndex,
      rank: [],
      totalRows: 0,
    },
    components: {
      'omegaup-schools-rank': schools_Rank,
    },
  });

  if (payload.isIndex) {
    API.School.getTopFiveSchoolsOfTheMonth()
      .then(data => {
        schoolsRank.rank = data.rank;
      })
      .catch(UI.apiError);
  } else {
    API.School.rank({
      offset: schoolsRank.page,
      rowcount: schoolsRank.length,
    })
      .then(data => {
        schoolsRank.totalRows = data.totalRows;
        schoolsRank.rank = data.rank;
      })
      .catch(UI.apiError);
  }
});
