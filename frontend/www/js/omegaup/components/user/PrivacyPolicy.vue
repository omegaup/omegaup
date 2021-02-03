<template>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="panel panel-default no-bottom-margin">
        <div class="panel-heading">
          <h3 class="panel-title">{{ T.wordsPrivacyPolicy }}</h3>
        </div>
        <div class="panel">
          <omegaup-markdown :markdown="policyMarkdown"></omegaup-markdown>
        </div>
      </div>
      <form @submit.prevent="$emit('submit', this)">
        <div class="top-margin text-center">
          <label
            ><input
              v-model="agreed"
              name="agreed"
              type="checkbox"
              :disabled="saved"
            />
            {{ T.wordsAgree }}</label
          >
          <button class="btn btn-primary" :disabled="!agreed || saved">
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
  @Prop({ default: false }) initialAgreed!: boolean;
  @Prop() saved!: boolean;

  T = T;
  agreed = this.initialAgreed;
}
</script>
