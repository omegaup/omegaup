<template>
  <div class="card">
    <h3 class="card-header">{{ T.wordsPrivacyPolicy }}</h3>
    <div class="card-body">
      <omegaup-markdown :markdown="policyMarkdown"></omegaup-markdown>
      <form @submit.prevent="$emit('submit')">
        <div class="top-margin text-center">
          <label class="mr-5"
            ><input
              v-model="currentAgreed"
              name="agreed"
              type="checkbox"
              :disabled="saved"
            />
            {{ T.wordsAgree }}</label
          >
          <button class="btn btn-primary" :disabled="!currentAgreed || saved">
            {{ T.wordsSaveChanges }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import T from '../../lang';

import omegaup_Markdown from '../Markdown.vue';

export default defineComponent({
  name: 'UserPrivacyPolicy',
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
  props: {
    policyMarkdown: {
      type: String,
      required: true,
    },
    agreed: {
      type: Boolean,
      default: false,
    },
    saved: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['submit'],
  data(): {
    T: typeof T;
    currentAgreed: boolean;
  } {
    return {
      T,
      currentAgreed: this.agreed,
    };
  },
  watch: {
    agreed(newValue: boolean): void {
      this.currentAgreed = newValue;
    },
  },
});
</script>
