import Vue from 'vue';
import { types } from '../api_types';
import user_CompareUsers from '../components/user/CompareUsers.vue';
import { OmegaUp } from '../omegaup';

interface UserCompareData {
  profile: types.UserProfileInfo;
  solvedProblemsCount: number;
  contestsCount: number;
}

interface UserComparePayload {
  user1: UserCompareData | null;
  user2: UserCompareData | null;
  username1: string | null;
  username2: string | null;
}

OmegaUp.on('ready', () => {
  const payload = JSON.parse(
    (document.getElementById('payload') as HTMLElement).innerText,
  ) as UserComparePayload;

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-compare': user_CompareUsers,
    },
    render: function (createElement) {
      return createElement('omegaup-user-compare', {
        props: {
          initialUser1: payload.user1,
          initialUser2: payload.user2,
          initialUsername1: payload.username1,
          initialUsername2: payload.username2,
        },
      });
    },
  });
});
