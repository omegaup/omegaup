<template>
  <ephemeral-ide
    :accepted-languages="acceptedLanguages"
    :initial-language="initialLanguage"
    :settings="problem.settings"
    :alias="problem.alias"
    :show-submit-button="canSubmit"
  />
</template>

<script lang="ts">
import { Component, Vue, Prop, Ref } from 'vue-property-decorator';
import Ephemeral from '../../graderv2/Ephemeral.vue';
import { types } from '../../api_types';

@Component({
  components: {
    'ephemeral-ide': Ephemeral,
  },
})
export default class EphemeralGrader extends Vue {
  @Ref() grader!: HTMLIFrameElement;
  @Prop() problem!: types.ProblemInfo;
  @Prop({ default: false }) canSubmit!: boolean;
  @Prop({ default: () => [] }) acceptedLanguages!: string[];
  @Prop({ default: 'cpp17-gcc' }) preferredLanguage!: string;

  loaded = false;
  get initialLanguage() {
    if (!this.acceptedLanguages.includes(this.preferredLanguage)) {
      return this.acceptedLanguages[0];
    }
    return this.preferredLanguage;
  }

  iframeLoaded(): void {
    this.loaded = true;
  }
}
</script>

<style lang="scss" scoped>
iframe {
  width: 100%;
  min-height: 60em;
}
</style>
