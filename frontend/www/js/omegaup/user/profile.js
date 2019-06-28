import Vue from 'vue';
import user_Profile from '../components/user/Profile.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  const profile = payload.profile;
  let viewProfile = new Vue({
    el: '#user-profile',
    render: function(createElement) {
      return createElement('omegaup-user-profile', {
        props: {
          profile: this.profile,
          contests: this.contests,
          solvedProblems: this.solvedProblems,
          unsolvedProblems: this.unsolvedProblems,
          visitorBadges: this.visitorBadges,
          profileBadges: this.profileBadges,
          rank: this.rank,
          charts: this.charts,
        }
      });
    },
    mounted: function() {
      API.User.contestStats({username: profile.username})
          .then(function(data) {
            let contests = [];
            for (var contest_alias in data['contests']) {
              var now = new Date();
              var currentTimestamp =
                  data['contests'][contest_alias]['finish_time'] * 1000;
              var end = OmegaUp.remoteTime(currentTimestamp);
              if (data['contests'][contest_alias]['place'] != null &&
                  now > end) {
                contests.push(data['contests'][contest_alias]);
              }
            }
            viewProfile.contests = contests;
          })
          .fail(UI.apiError);

      API.User.problemsSolved({username: profile.username})
          .then(function(data) {
            viewProfile.solvedProblems = data['problems'];
          })
          .fail(UI.apiError);

      API.User.listUnsolvedProblems({username: profile.username})
          .then(function(data) {
            viewProfile.unsolvedProblems = data['problems'];
          })
          .fail(UI.apiError);

      if (payload.logged_in) {
        API.Badge.myList({})
            .then(function(data) {
              viewProfile.visitorBadges = data['badges'];
            })
            .fail(UI.apiError);
      }

      API.Badge.userList({target_username: profile.username})
          .then(function(data) { viewProfile.profileBadges = data['badges']; })
          .fail(UI.apiError);

      API.User.stats({username: profile.username})
          .then(function(data) { viewProfile.charts = data; })
          .fail(omegaup.UI.apiError);
    },
    data: {
      profile: profile,
      contests: [],
      profileBadges: [],
      solvedProblems: [],
      unsolvedProblems: [],
      visitorBadges: [],
      charts: null,
    },
    computed: {
      rank: function() {
        switch (profile.classname) {
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
