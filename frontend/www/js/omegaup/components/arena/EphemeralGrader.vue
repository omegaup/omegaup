<template>
  <ephemeral-ide
    :accepted-languages="acceptedLanguages"
    :initial-language="initialLanguage"
    :problem="problem"
    :can-submit="canSubmit"
    :can-run="canRun"
    :is-embedded="isEmbedded"
    :initial-theme="initialTheme"
  >
  </ephemeral-ide>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as Util from '../../grader/util';
import Ephemeral from '../../grader/Ephemeral.vue';

@Component({
  components: {
    'ephemeral-ide': Ephemeral,
  },
})
export default class EphemeralGrader extends Vue {
  @Prop({ default: () => ({ ...Util.DUMMY_PROBLEM }) })
  problem!: types.ProblemInfo;
  @Prop({ default: false }) canSubmit!: boolean;
  @Prop({ default: true }) canRun!: boolean;
  @Prop({
    default: () =>
      Object.values(Util.supportedLanguages).map(
        (languageInfo) => languageInfo.language,
      ),
  })
  acceptedLanguages!: string[];
  @Prop({ default: 'cpp17-gcc' }) preferredLanguage!: string;
  @Prop({ default: true }) isEmbedded!: boolean;
  @Prop({ default: Util.MonacoThemes.VSLight })
  initialTheme!: Util.MonacoThemes;

  // note: initial source is for the IDE is also supported
  get initialLanguage() {
    if (!this.acceptedLanguages.includes(this.preferredLanguage)) {
      return this.acceptedLanguages[0];
    }
    return this.preferredLanguage;
  }
}
</script>
