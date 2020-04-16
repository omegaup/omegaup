import Vue from 'vue';
import problem_New from '../components/problem/Form.vue';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';
import T from '../lang';
import API from '../api.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let problemNew = new Vue({
    el: '#problem-new',
    render: function(createElement) {
      return createElement('omegaup-problem-new', {
        props: {
          isUpdate: this.isUpdate,
          requestURI: this.requestURI,
          problemAlias: this.problemAlias,
          initialTitle: this.title,
          initialAlias: this.alias,
          initialTimeLimit: this.timeLimit,
          initialExtraWallTime: this.extraWallTime,
          initialMemoryLimit: this.memoryLimit,
          initialOutputLimit: this.outputLimit,
          initialInputLimit: this.inputLimit,
          initialOverallWallTimeLimit: this.overallWallTimeLimit,
          initialValidatorTimeLimit: this.validatorTimeLimit,
          validLanguages: this.validLanguages,
          validatorTypes: this.validatorTypes,
          initialEmailClarifications: this.emailClarifications,
          initialVisibility: this.visibility,
          initialAllowUserAddTags: this.allowUserAddTags,
          initialSource: this.source,
          initialValidator: this.validator,
          initialLanguages: this.languages,
          initialTags: this.tags,
          initialSelectedTags: this.selectedTags,
        },
        on: {
          'alias-in-use': alias => {
            API.Problem.details({ problem_alias: alias })
              .then(data => {
                if (!data.exists) {
                  ui.dismissNotifications();
                  return;
                }
                ui.error(
                  ui.formatString(T.aliasAlreadyInUse, {
                    alias: ui.escape(alias),
                  }),
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    data: {
      isUpdate: payload.isUpdate,
      requestURI: payload.requestURI,
      problemAlias: payload.problemAlias,
      title: payload.title,
      alias: payload.alias,
      timeLimit: payload.timeLimit,
      extraWallTime: payload.extraWallTime,
      memoryLimit: payload.memoryLimit,
      outputLimit: payload.outputLimit,
      inputLimit: payload.inputLimit,
      overallWallTimeLimit: payload.overallWallTimeLimit,
      validatorTimeLimit: payload.validatorTimeLimit,
      validLanguages: payload.validLanguages,
      validatorTypes: payload.validatorTypes,
      emailClarifications: payload.emailClarifications,
      visibility: payload.visibility,
      allowUserAddTags: payload.allowUserAddTags,
      source: payload.source,
      validator: payload.validator,
      languages: payload.languages,
      tags: payload.tags,
      selectedTags: JSON.parse(payload.selectedTags),
    },
    components: {
      'omegaup-problem-new': problem_New,
    },
  });
});
