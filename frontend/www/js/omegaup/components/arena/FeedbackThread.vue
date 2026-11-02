<template>
  <div v-if="!currentSaved" class="form-group" @keydown="onHandleKeyDown">
    <input
      v-if="!isSelectedNewFeedback"
      class="form-control"
      type="text"
      :placeholder="T.runDetailsFeedbackThreadPlaceholder"
      @click="isSelectedNewFeedback = true"
    />
    <div v-else class="card">
      <div class="card-header">
        <omegaup-user-username
          :classname="currentFeedbackThread.authorClassname"
          :username="currentFeedbackThread.author"
          :linkify="true"
        ></omegaup-user-username>
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
          v-model="currentFeedbackThread.text"
          :placeholder="T.runDetailsFeedbackThreadPlaceholder"
          class="w-100"
        ></textarea>
      </div>
      <div class="card-footer text-muted">
        <div class="form-group my-2">
          <button
            data-button-submit
            :disabled="!currentFeedbackThread.text"
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
    <div class="card-header">
      <omegaup-user-username
        :classname="currentFeedbackThread.authorClassname"
        :username="currentFeedbackThread.author"
        :linkify="true"
      ></omegaup-user-username>
      {{ currentFeedbackThreadTimestamp }}
    </div>
    <div class="card-body">
      <omegaup-markdown
        :markdown="currentFeedbackThread.text"
        :full-width="true"
      ></omegaup-markdown>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import T from '../../lang';
import omegaup_Markdown from '../Markdown.vue';
import user_Username from '../user/Username.vue';
import * as time from '../../time';
import { ArenaCourseFeedback, FeedbackStatus } from './Feedback.vue';

export default defineComponent({
  name: 'FeedbackThread',
  components: {
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-user-username': user_Username,
  },
  props: {
    feedbackThread: {
      type: Object as PropType<ArenaCourseFeedback>,
      required: true,
    },
    saved: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['submit-feedback-thread'],
  data() {
    return {
      FeedbackStatus,
      T,
      time,
      currentSaved: this.saved,
      currentFeedbackThread: this.feedbackThread,
      isSelectedNewFeedback: false,
    };
  },
  computed: {
    currentFeedbackThreadTimestamp(): string {
      return time.formatDateTimeLocal(
        this.currentFeedbackThread.timestamp ?? new Date(),
      );
    },
  },
  watch: {
    isSelectedNewFeedback(newValue: boolean): void {
      if (newValue) {
        this.$nextTick(() => {
          (this.$refs['feedback-thread-form'] as HTMLTextAreaElement)?.focus();
        });
      }
    },
  },
  methods: {
    onHandleKeyDown(event: KeyboardEvent): void {
      if (event.key === 'Escape') {
        this.onDeleteFeedbackThread();
      }
    },
    onSubmitFeedback(): void {
      this.currentSaved = true;
      this.$emit('submit-feedback-thread', this.currentFeedbackThread);
    },
    onDeleteFeedbackThread(): void {
      this.currentFeedbackThread.text = '';
      this.isSelectedNewFeedback = false;
    },
  },
});
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
