<template>
  <div class="card">
    <div class="card-header">
      {{ !saved ? T.runDetailsNewFeedback : T.runDetailsFeedbackCreated }}
    </div>
    <div class="card-body">
      <textarea
        v-if="!saved"
        ref="feedback-form"
        v-model="text"
        :placeholder="T.runDetailsFeedbackPlaceholder"
        class="w-100"
      ></textarea>
      <omegaup-markdown
        v-else
        :markdown="text"
        :full-width="true"
      ></omegaup-markdown>
    </div>
    <div v-if="!saved" class="card-footer text-muted">
      <div class="form-group my-2">
        <button
          data-button-submit
          :disabled="!text"
          class="btn btn-primary mx-2"
          @click.prevent="onSubmitFeedback"
        >
          {{ T.runDetailsFeedbackAddReview }}
        </button>
        <button
          data-button-cancel
          class="btn btn-danger mx-2"
          @click.prevent="onCancelFeedback"
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
import omegaup_Markdown from '../Markdown.vue';

export enum FeedbackStatus {
  New = 'New',
  InProgress = 'InProgress',
  Saved = 'Saved',
}

export interface ArenaCourseFeedback {
  line: number;
  text: null | string;
  status: FeedbackStatus;
}

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class Feedback extends Vue {
  @Prop() feedback!: ArenaCourseFeedback;
  @Ref('feedback-form') feedbackForm!: HTMLTextAreaElement;

  FeedbackStatus = FeedbackStatus;
  T = T;
  text = this.feedback.text;
  saved: boolean = false;

  mounted() {
    this.$nextTick(() => this.feedbackForm.focus());
  }

  onSubmitFeedback() {
    this.saved = true;
    this.$emit('submit', {
      ...this.feedback,
      text: this.text,
      status: FeedbackStatus.InProgress,
    });
  }

  onCancelFeedback() {
    this.$emit('cancel', this.feedback);
  }
}
</script>
