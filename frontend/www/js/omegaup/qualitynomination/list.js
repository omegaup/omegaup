import Vue from 'vue';
import qualitynomination_List from '../components/qualitynomination/List.vue';
import { OmegaUp } from '../omegaup';
import API from '../api.js';
import * as UI from '../ui';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);

  let nominationsList = new Vue({
    el: '#qualitynomination-list',
    render: function(createElement) {
      return createElement('omegaup-qualitynomination-list', {
        props: {
          page: payload.page,
          length: payload.length,
          myView: payload.myView,
          nominations: this.nominations,
          totalRows: this.totalRows,
        },
      });
    },
    data: {
      nominations: [],
      totalRows: 0,
      page: payload.page,
      length: payload.length,
      myView: payload.myView,
    },
    components: {
      'omegaup-qualitynomination-list': qualitynomination_List,
    },
  });

  if (!payload.myView) {
    API.QualityNomination.list({
      offset: nominationsList.page,
      rowcount: nominationsList.length,
    })
      .then(data => {
        nominationsList.totalRows = data.totalRows;
        nominationsList.nominations = data.nominations;
      })
      .catch(UI.apiError);
  } else {
    API.QualityNomination.myList({
      offset: nominationsList.page,
      rowcount: nominationsList.length,
    })
      .then(data => {
        nominationsList.totalRows = data.totalRows;
        nominationsList.nominations = data.nominations;
      })
      .catch(UI.apiError);
  }
});
