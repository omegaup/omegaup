<template>
  <iframe
    ref="grader"
    class="mt-2 border border-white"
    src="/grader/ephemeral/index-light.html?embedded"
  ></iframe>
</template>

<script lang="ts">
import { Component, Vue, Prop, Ref, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';

@Component
export default class EphemeralGrader extends Vue {
  @Ref() grader!: HTMLIFrameElement;
  @Prop() problem!: types.ProblemInfo;
  @Prop({ default: false }) canSubmit!: boolean;
  @Prop({ default: () => [] }) acceptedLanguages!: string[];
  @Prop({ default: 'cpp17-gcc' }) preferredLanguage!: string;

  loaded = false;

  mounted(): void {
    (this.$refs.grader as HTMLIFrameElement).onload = () => {
      this.iframeLoaded();
      const languageSelectElement: HTMLSelectElement = ((this.$refs
        .grader as HTMLIFrameElement)
        .contentWindow as Window).document.getElementById(
        'language',
      ) as HTMLSelectElement;
      languageSelectElement.value = this.preferredLanguage;
    };
  }

  @Watch('problem')
  onProblemChanged() {
    if (!this.loaded) {
      // This will be updated when the component is mounted.
      return;
    }
    this.setSettings();
  }

  setSettings(): void {
    ((this.$refs.grader as HTMLIFrameElement)
      .contentWindow as Window).postMessage(
      {
        method: 'setSettings',
        params: {
          alias: this.problem.alias,
          settings: this.problem.settings,
          languages: this.acceptedLanguages,
          showSubmitButton: this.canSubmit,
        },
      },
      `${window.location.origin}/grader/ephemeral/embedded/`,
    );
  }

  iframeLoaded(): void {
    this.loaded = true;
    this.setSettings();
  }
}
</script>

<style lang="scss" scoped>
iframe {
  width: 100%;
  min-height: 40em;
}
</style>
