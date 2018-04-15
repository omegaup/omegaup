import Vue from 'vue';
import user_Profile from '../components/user/Profile.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  const user_profile = JSON.parse(document.getElementById('profile').innerText);
  let viewProfile = new Vue({
    el: '#user-profile',
    render: function(createElement) {
      return createElement('omegaup-user-profile', {
        props: {
          profile: this.profile,
          contests: this.contests,
          solved_problems: this.solved_problems,
          unsolved_problems: this.unsolved_problems,
          rank: this.rank,
          charts: this.charts,
        }
      });
    },
    mounted: function() {
      API.User.contestStats({username: user_profile.username})
          .then(function(data) {
            let contests = [];
            for (var contest_alias in data['contests']) {
              var now = new Date();
              var end = OmegaUp.remoteTime(
                  data['contests'][contest_alias]['data']['finish_time'] *
                  1000);
              if (data['contests'][contest_alias]['place'] != null &&
                  now > end) {
                contests.push(data['contests'][contest_alias]);
              }
            }
            viewProfile.contests = contests;
          })
          .fail(UI.apiError);

      API.User.problemsSolved({username: user_profile.username})
          .then(function(data) {
            viewProfile.solved_problems = data['problems'];
          })
          .fail(UI.apiError);

      API.User.listUnsolvedProblems({username: user_profile.username})
          .then(function(data) {
            viewProfile.unsolved_problems = data['problems'];
          })
          .fail(UI.apiError);

      API.User.stats({username: user_profile.username})
          .then(function(data) { viewProfile.charts = data; })
          .fail(omegaup.UI.apiError);
    },
    data: {
      profile: user_profile,
      contests: null,
      solved_problems: null,
      unsolved_problems: null,
      charts: null,
    },
    computed: {
      rank: function() {
        switch (user_profile.classname) {
          case 'user-rank-unranked':
            return T.profileRankUnrated;
          case 'user-rank-beginner':
            return T.profileRankBeginner;
          case 'user-rank-specialist':
            return T.profileRankSpecialist;
          case 'user-rank-expert':
            return T.profileRankExpert;
          case 'user-rank-master':
            return T.profileRankMaster;
          case 'user-rank-international-master':
            return T.profileRankInternationalMaster;
        }
      }
    },
    components: {
      'omegaup-user-profile': user_Profile,
    },
  });
});
