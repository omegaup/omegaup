import Vue from 'vue';

import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

import user_Profile from '../components/user/Profilev2.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserProfileDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();

  const fromLogin =
    new URL(document.location.toString()).searchParams.get('fromLogin') !==
    null;

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-profile': user_Profile,
    },
    render: function (createElement) {
      return createElement('omegaup-user-profile', {
        props: {
          data: payload.extraProfileDetails,
          profile: payload.profile,
          profileBadges: new Set(
            payload.extraProfileDetails?.ownedBadges?.map(
              (badge) => badge.badge_alias,
            ),
          ),
          visitorBadges: new Set(payload.extraProfileDetails?.badges),
          fromLogin: fromLogin,
          userTypes: commonPayload.userTypes,
        },
        on: {
          'update-user-objectives': ({
            hasCompetitiveObjective,
            hasLearningObjective,
            hasScholarObjective,
            hasTeachingObjective,
          }: {
            hasCompetitiveObjective: string;
            hasLearningObjective: string;
            hasScholarObjective: string;
            hasTeachingObjective: string;
          }) => {
            api.User.update({
              has_competitive_objective: hasCompetitiveObjective,
              has_learning_objective: hasLearningObjective,
              has_scholar_objective: hasScholarObjective,
              has_teaching_objective: hasTeachingObjective,
            })
              .then(() => {
                ui.success(T.userObjectivesUpdateSuccess);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  if (fromLogin && commonPayload.userTypes.length === 0) {
    $('.objectivesQuestionsModal').modal();
  }
});
