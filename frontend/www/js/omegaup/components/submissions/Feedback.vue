<template>
  <div>
    <div v-if="generalFeedback" data-submission-feedback>
      <h3>{{ T.feedbackTitle }}</h3>
      <pre data-run-feedback><code>{{ generalFeedback.feedback }}</code></pre>
      {{
        ui.formatString(T.feedbackLeftBy, {
          date: time.formatDate(generalFeedback.date),
        })
      }}
      <omegaup-user-username
        :username="generalFeedback.author"
        :classname="generalFeedback.author_classname"
        :linkify="true"
      ></omegaup-user-username>
    </div>
    <div v-if="isAdmin" class="feedback-section">
      <a
        data-run-leave-feedback-button
        role="button"
        class="btn btn-sm btn-primary"
        @click="showFeedbackForm = !showFeedbackForm"
        >{{ T.submissionFeedbackSendButton }}</a
      >
      <div v-show="showFeedbackForm" class="form-group">
        <p>{{ T.submissionFeedbackAnimationButton }}</p>
        <img
          v-show="showFeedbackForm"
          src="/media/submission_feedback_demo.gif"
          :alt="T.submissionFeedbackDemoAlt"
        />
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
  @Prop({ default: () => [] }) feedbackOptions!: types.SubmissionFeedback[];

  T = T;
  ui = ui;
  time = time;

  showFeedbackForm = false;
  feedback = this.generalFeedback?.feedback ?? null;

  get generalFeedback(): null | types.SubmissionFeedback {
    if (!this.feedbackOptions.length) return null;
    const [feedback] = this.feedbackOptions.filter(
      (feedback) => feedback.range_bytes_start == null,
    );
    return feedback;
  }
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
