import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import coderofthemonth_List from '../components/coderofthemonth/List.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CoderOfTheMonthPayload();
  const locationHash = window.location.hash.substring(1).split('/')[0];
  const initialTab = getSelectedValidTab(locationHash);
  if (initialTab !== locationHash) {
    history.replaceState(null, '', `#${initialTab}`);
  }
  const coderOfTheMonthList = new Vue({
    el: '#main-container',
    components: {
      'omegaup-coder-of-the-month-list': coderofthemonth_List,
    },
    data: () => ({
      coderIsSelected:
        payload.isMentor && payload.options && payload.options.coderIsSelected,
      selectedTab: getSelectedValidTab(
        window.location.hash.substring(1).split('/')[0],
      ),
    }),
    render: function (createElement) {
      return createElement('omegaup-coder-of-the-month-list', {
        props: {
          codersOfCurrentMonth: payload.codersOfCurrentMonth,
          codersOfPreviousMonth: payload.codersOfPreviousMonth,
          candidatesToCoderOfTheMonth: payload.candidatesToCoderOfTheMonth,
          isMentor: payload.isMentor,
          selectedTab: this.selectedTab,
          canChooseCoder: payload.isMentor && payload.options?.canChooseCoder,
          coderIsSelected: this.coderIsSelected,
          category: payload.category,
        },
        on: {
          'select-coder': (coderUsername: string, category: string) => {
            api.User.selectCoderOfTheMonth({
              username: coderUsername,
              category: category,
            })
              .then(() => {
                ui.success(
                  payload.category == 'all'
                    ? T.coderOfTheMonthSelectedSuccessfully
                    : T.coderOfTheMonthFemaleSelectedSuccessfully,
                );
                coderOfTheMonthList.coderIsSelected = true;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  // Handle browser back/forward button navigation
  // Same pattern used in frontend/www/js/omegaup/user/profile.ts
  const onHashChange = () => {
    const hash = window.location.hash.substring(1).split('/')[0];
    const validTab = getSelectedValidTab(hash);
    coderOfTheMonthList.selectedTab = validTab;
  };

  window.addEventListener('hashchange', onHashChange);

  function getSelectedValidTab(tab: string): string {
    const validTabs = [
      'codersOfTheMonth',
      'codersOfPreviousMonth',
      'candidatesToCoderOfTheMonth',
    ];
    const defaultTab = 'codersOfTheMonth';
    const isValidTab = validTabs.includes(tab);
    return isValidTab ? tab : defaultTab;
  }
});
