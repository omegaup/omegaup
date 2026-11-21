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
      <p data-submission-feedback-review-hint>
        {{ T.submissionFeedbackReviewHint }}
      </p>
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
}
</style>
