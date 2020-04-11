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
          pages: this.pages,
          length: payload.length,
          myView: payload.myView,
          nominations: this.nominations,
          pagerItems: this.pagerItems,
        },
        on: {
          goToPage: pageNumber => {
            if (pageNumber > 0) {
              showNominations(pageNumber);
            }
          },
        },
      });
    },
    data: {
      nominations: [],
    },
    components: {
      'omegaup-qualitynomination-list': qualitynomination_List,
    },
  });

  function showNominations(pageNumber) {
    if (!payload.myView) {
      API.QualityNomination.list({
        offset: pageNumber,
        rowcount: payload.length,
      })
        .then(data => {
          nominationsList.nominations = data.nominations;
          nominationsList.pagerItems = data.pager_items;
          nominationsList.pages = pageNumber;
        })
        .catch(UI.apiError);
    } else {
      API.QualityNomination.myList({
        offset: pageNumber,
        rowcount: payload.length,
      })
        .then(data => {
          nominationsList.nominations = data.nominations;
          nominationsList.pagerItems = data.pager_items;
          nominationsList.pages = pageNumber;
        })
        .catch(UI.apiError);
    }
  }

  showNominations(1);
});
