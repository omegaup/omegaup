import Vue from 'vue';
import user_Language from '../components/user/Language.vue';
import {OmegaUp, T, UI, API} from '../omegaup.js';

OmegaUp.on('ready', function() {
  const userLanguage = JSON.parse(document.getElementById('payloadLanguage').innerText);
  let viewLanguage = new Vue({
    el: '#user-language',
    render: function(createElement) {
      return createElement('omegaup-user-language', {
        props: {
          selectedLanguage: this.selectedLanguage,
          availableLanguages: this.availableLanguages,
        },
        on: {
          'change-language': function(language) {
            API.User.updateLanguage({language: language})
            .then(function(data) { if (data.status == 'ok') { window.location.reload(); }})
                .fail(UI.apiError);
          },
        }
      });
    },
    data: {
      selectedLanguage: userLanguage.selectedLanguage,
      availableLanguages: userLanguage.availableLanguages,
    },
    components: {
      'omegaup-user-language': user_Language,
    },
  });
});
