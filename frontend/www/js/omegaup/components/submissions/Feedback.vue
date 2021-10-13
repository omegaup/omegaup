<template>
  <div v-if="isAdmin || feedbackOptions" data-submission-feedback>
    <h3>{{ T.feedbackTitle }}</h3>
    <pre><code>{{
      feedbackOptions ? feedbackOptions.feedback : T.feedbackNotSentYet
    }}</code></pre>
    <div v-if="feedbackOptions">
      {{
        ui.formatString(T.feedbackLeftBy, {
          date: time.formatDate(feedbackOptions.date),
        })
      }}
      <omegaup-user-username
        :username="feedbackOptions.author"
        :classname="feedbackOptions.author_classname"
        :linkify="true"
      ></omegaup-user-username>
    </div>
    <div v-if="isAdmin" class="feedback-section">
      <a role="button" @click="showFeedbackForm = !showFeedbackForm">{{
        !feedbackOptions
          ? T.submissionFeedbackSendButton
          : T.submissionFeedbackUpdateButton
      }}</a>
      <div v-show="showFeedbackForm" class="form-group">
        <textarea
          v-model="feedback"
          class="form-control"
          rows="3"
          maxlength="200"
        ></textarea>
        <button
          class="btn btn-sm btn-primary"
          :disabled="!feedback"
          @click.prevent="
            $emit('set-feedback', {
              guid,
              feedback,
              isUpdate: Boolean(feedbackOptions),
            })
          "
        >
          {{
            !feedbackOptions
              ? T.submissionSendFeedback
              : T.submissionUpdateFeedback
          }}
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';

import user_Username from '../user/Username.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class SubmissionFeedback extends Vue {
  @Prop() guid!: string;
  @Prop({ default: false }) isAdmin!: boolean;
  @Prop({ default: null }) feedbackOptions!: null | types.SubmissionFeedback;

  T = T;
  ui = ui;
  time = time;

  showFeedbackForm = false;
  feedback = this.feedbackOptions?.feedback ?? null;
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';
.feedback-section {
  margin-top: 1.5em;
  .form-group {
    margin-top: 0.5em;
    button {
      margin-top: 1em;
    }
  }
}
</style>
