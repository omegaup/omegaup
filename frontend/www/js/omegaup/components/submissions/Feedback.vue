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
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';

import user_Username from '../user/Username.vue';

function findGeneralFeedback(
  feedbackOptions: types.SubmissionFeedback[],
): null | types.SubmissionFeedback {
  if (!feedbackOptions.length) return null;
  const [feedback] = feedbackOptions.filter(
    (feedback) => feedback.range_bytes_start == null,
  );
  return feedback;
}

export default defineComponent({
  name: 'SubmissionFeedback',
  components: {
    'omegaup-user-username': user_Username,
  },
  props: {
    guid: {
      type: String,
      required: true,
    },
    isAdmin: {
      type: Boolean,
      default: false,
    },
    feedbackOptions: {
      type: Array as PropType<types.SubmissionFeedback[]>,
      default: () => [],
    },
  },
  data() {
    return {
      T,
      ui,
      time,
      showFeedbackForm: false,
      // Computed properties are not available yet inside data(), so the
      // initial value is derived from the prop directly.
      feedback: findGeneralFeedback(this.feedbackOptions)?.feedback ?? null,
    };
  },
  computed: {
    generalFeedback(): null | types.SubmissionFeedback {
      return findGeneralFeedback(this.feedbackOptions);
    },
  },
});
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
