import Vue from 'vue';
import qualitynomination_List from '../components/qualitynomination/List.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as UI from '../ui';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  const headerPayload = JSON.parse(
    document.getElementById('header-payload').innerText,
  );

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
          isAdmin: headerPayload.isAdmin,
        },
        on: {
          goToPage: (pageNumber, status) => {
            if (pageNumber > 0) {
              showNominations(pageNumber, status);
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

  function showNominations(pageNumber, status) {
    if (!payload.myView) {
      api.QualityNomination.list({
        offset: pageNumber,
        rowcount: payload.length,
        status: status,
      })
        .then(data => {
          nominationsList.nominations = data.nominations;
          nominationsList.pagerItems = data.pager_items;
          nominationsList.pages = pageNumber;
        })
        .catch(UI.apiError);
    } else {
      api.QualityNomination.myList({
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

  showNominations(1, 'all');
});
