<template>
  <div v-if="!saved" class="form-group" @keydown="onHandleKeyDown">
    <input
      v-if="!isSelectedNewFeedback"
      class="form-control"
      type="text"
      :placeholder="T.runDetailsFeedbackThreadPlaceholder"
      @click="isSelectedNewFeedback = true"
    />
    <div v-else class="card">
      <div class="card-header">
        <button
          class="close btn-sm"
          type="button"
          @click.prevent="onDeleteFeedbackThread"
        >
          ❌
        </button>
      </div>
      <div class="card-body">
        <textarea
          ref="feedback-thread-form"
          v-model="currentFeedback.text"
          :placeholder="T.runDetailsFeedbackThreadPlaceholder"
          class="w-100"
        ></textarea>
      </div>
      <div class="card-footer text-muted">
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
            @click.prevent="onDeleteFeedbackThread"
          >
            {{ T.runDetailsFeedbackCancel }}
          </button>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="card">
    <div class="card-header"></div>
    <div class="card-body">
      <omegaup-markdown
        :markdown="currentFeedback.text"
        :full-width="true"
      ></omegaup-markdown>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref, Watch } from 'vue-property-decorator';
import T from '../../lang';
import omegaup_Markdown from '../Markdown.vue';
import user_Username from '../user/Username.vue';
import * as time from '../../time';
import { ArenaCourseFeedback, FeedbackStatus } from './Feedback.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-user-username': user_Username,
  },
})
export default class Feedback extends Vue {
  @Prop() feedback!: ArenaCourseFeedback;
  @Ref('feedback-thread-form') feedbackThreadForm!: HTMLTextAreaElement;

  FeedbackStatus = FeedbackStatus;
  T = T;
  time = time;
  saved = false;
  currentFeedback = this.feedback;
  isSelectedNewFeedback = false;

  onHandleKeyDown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
      this.onDeleteFeedbackThread();
    }
  }

  onSubmitFeedback() {
    this.saved = true;
    this.$emit('submit-feedback-thread', this.currentFeedback);
  }

  onDeleteFeedbackThread() {
    this.currentFeedback.text = '';
    this.isSelectedNewFeedback = false;
  }

  @Watch('isSelectedNewFeedback')
  onIsSelectedNewFeedbackChanged(newValue: boolean): void {
    if (newValue) {
      this.$nextTick(() => this.feedbackThreadForm.focus());
    }
  }
}
</script>

<style>
.card {
  margin-bottom: 0.5rem;
}

.card-header {
  padding: 0.5rem 1.25rem;
}

.card-body {
  padding: 0.5rem 1.25rem;
}

[data-markdown-statement] p {
  margin-bottom: 0;
}
</style>
