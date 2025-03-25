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
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class UserPrivacyPolicy extends Vue {
  @Prop() policyMarkdown!: string;
  @Prop({ default: false }) agreed!: boolean;
  @Prop() saved!: boolean;

  T = T;
  currentAgreed = this.agreed;
}
</script>
