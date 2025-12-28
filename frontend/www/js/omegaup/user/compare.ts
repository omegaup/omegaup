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
  const payloadElement = document.getElementById('payload');
  if (!payloadElement) {
    console.error('Payload element not found');
    return;
  }

  let payload: UserComparePayload;
  try {
    payload = JSON.parse(payloadElement.innerText) as UserComparePayload;
  } catch (e) {
    console.error('Failed to parse payload JSON:', e);
    return;
  }

  const containerElement = document.getElementById('main-container');
  if (!containerElement) {
    console.error('Main container element not found');
    return;
  }

  new Vue({
    el: containerElement,
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
