import Vue from 'vue';
import qualitynomination_List from '../components/qualitynomination/List.vue';
import { OmegaUp, omegaup } from '../omegaup';
import * as api from '../api';
import * as UI from '../ui';
import { types } from '../api_types';
import T from '../lang';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(
    (<HTMLElement>document.getElementById('payload')).innerText,
  );
  const headerPayload = JSON.parse(
    (<HTMLElement>document.getElementById('header-payload')).innerText,
  );

  let nominationsList = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-qualitynomination-list', {
        props: {
          pages: this.pages,
          nominations: this.nominations,
          pagerItems: this.pagerItems,
          length: payload.length,
          myView: payload.myView,
          isAdmin: headerPayload.isAdmin,
        },
        on: {
          goToPage: (
            pageNumber: number,
            status: string,
            query: string,
            column: string,
          ) => {
            if (pageNumber > 0) {
              showNominations(pageNumber, status, query, column);
            }
          },
        },
      });
    },
    data: {
      nominations: <types.NominationListItem[]>[],
      pagerItems: <types.PageItem[]>[],
      pages: 1,
    },
    components: {
      'omegaup-qualitynomination-list': qualitynomination_List,
    },
  });

  function showNominations(
    pageNumber: number,
    status: string,
    query: string,
    column: string,
  ) {
    if (!payload.myView) {
      if (query != '' && column != '') {
        api.QualityNomination.list({
          offset: pageNumber,
          rowcount: payload.length,
          status: status,
          query: query,
          column: column,
        })
          .then(data => {
            nominationsList.nominations = data.nominations ?? [];
            nominationsList.pagerItems = data.pagerItems;
            nominationsList.pages = pageNumber;
          })
          .catch(UI.apiError);
      } else {
        api.QualityNomination.list({
          offset: pageNumber,
          rowcount: payload.length,
          status: status,
        })
          .then(data => {
            nominationsList.nominations = data.nominations ?? [];
            nominationsList.pagerItems = data.pagerItems;
            nominationsList.pages = pageNumber;
          })
          .catch(UI.apiError);
      }
    } else {
      api.QualityNomination.myList({
        offset: pageNumber,
        rowcount: payload.length,
      })
        .then(data => {
          nominationsList.nominations = data.nominations ?? [];
          nominationsList.pagerItems = data.pagerItems;
          nominationsList.pages = pageNumber;
        })
        .catch(UI.apiError);
    }
  }

  showNominations(1, 'all', '', '');
});
