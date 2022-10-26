<template>
  <div class="card">
    <div class="card-header">{{ T.runDetailsNewFeedback }}</div>
    <div class="card-body">
      <textarea
        ref="feedback-form"
        v-model="text"
        :placeholder="T.runDetailsFeedbackPlaceholder"
        class="w-100"
      ></textarea>
    </div>
    <div class="card-footer text-muted">
      <div class="form-group my-2">
        <button
          data-button-submit
          :disabled="!text"
          class="btn btn-primary mx-2"
          @click.prevent="
            $emit('submit', {
              ...feedback,
              text,
              status: FeedbackStatus.InProgress,
            })
          "
        >
          {{ T.runDetailsFeedbackAddReview }}
        </button>
        <button
          data-button-cancel
          class="btn btn-danger mx-2"
          @click.prevent="
            $emit('cancel', { ...feedback, status: FeedbackStatus.New })
          "
        >
          {{ T.runDetailsFeedbackCancel }}
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref } from 'vue-property-decorator';
import T from '../../lang';

export enum FeedbackStatus {
  New = 'New',
  InProgress = 'InProgress',
  Saved = 'Saved',
}

export interface ArenaCourseFeedback {
  line: null | number;
  text: null | string;
  status: FeedbackStatus;
}

@Component
export default class Feedback extends Vue {
  @Prop() feedback!: ArenaCourseFeedback;
  @Ref('feedback-form') feedbackForm!: HTMLTextAreaElement;

  FeedbackStatus = FeedbackStatus;
  T = T;
  text = this.feedback.text;

  mounted() {
    this.feedbackForm.focus();
  }
}
</script>
