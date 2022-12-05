<template>
  <div class="card">
    <div class="card-header">
      {{ !saved ? T.runDetailsNewFeedback : T.runDetailsFeedbackCreated }}
      <button
        v-if="saved"
        class="close btn-sm"
        type="button"
        @click.prevent="onDeleteFeedback"
      >
        ❌
      </button>
    </div>
    <div class="card-body">
      <textarea
        v-if="!saved"
        ref="feedback-form"
        v-model="currentFeedback.text"
        :placeholder="T.runDetailsFeedbackPlaceholder"
        class="w-100"
      ></textarea>
      <omegaup-markdown
        v-else
        :markdown="currentFeedback.text"
        :full-width="true"
      ></omegaup-markdown>
    </div>
    <div v-if="!saved" class="card-footer text-muted">
      <div class="form-group my-2">
        <button
          data-button-submit
          :disabled="!currentFeedback.text"
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
  Updated = 'Updated',
  Deleted = 'Deleted',
}

export interface ArenaCourseFeedback {
  lineNumber: number;
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
  saved: boolean = this.feedback.status == FeedbackStatus.Saved;
  currentFeedback = this.feedback;

  mounted() {
    if (this.feedback.status === FeedbackStatus.Saved) return;
    this.$nextTick(() => this.feedbackForm.focus());
  }

  onSubmitFeedback() {
    this.saved = true;
    this.currentFeedback.status = FeedbackStatus.InProgress;
    this.$emit('submit', this.currentFeedback);
  }

  onCancelFeedback() {
    this.currentFeedback.text = null;
    this.$emit('cancel');
  }

  onDeleteFeedback() {
    this.currentFeedback.text = null;
    this.$emit('delete', this.currentFeedback);
  }
}
</script>
