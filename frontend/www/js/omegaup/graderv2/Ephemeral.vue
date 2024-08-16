<template>
  <div>
    <div class="navbar navbar-light bg-light py-0 border-bottom">
      <span class="navbar-brand">
        omegaUp ephemeral grader
        <sup>&alpha;</sup>
      </span>
      <form class="form-inline my-2 my-lg-0 ephemeral-form">
        <label v-if="isUploadButton" for="upload">
          <a class="btn btn-secondary btn-sm mr-sm-2" role="button">
            <span class="fa fa-upload" title="Upload" aria-hidden="true"></span>
          </a>
        </label>

        <label v-if="isDownloadButton" for="download">
          <a class="btn btn-secondary btn-sm mr-sm-2" role="button">
            <span
              class="fa fa-download"
              title="Download"
              aria-hidden="true"
            ></span>
          </a>
        </label>

        <select
          v-model="selectedLanguage"
          class="form-control form-control-sm mr-sm-2"
          data-language-select
        >
          <option
            v-for="language in languages"
            :key="language"
            :value="language"
          >
            {{ getLanguageName(language) }}
          </option>
        </select>

        <button
          v-if="isRunButton"
          class="btn btn-sm btn-secondary mr-2 my-sm-0 ephemeral-button"
          data-run-button
          @click.prevent="handleRun"
        >
          <span>Run</span>
          <img
            v-if="isRunLoading"
            src="https://samherbert.net/svg-loaders/svg-loaders/tail-spin.svg"
            height="16"
          />
        </button>
        <button
          v-if="isSubmitButton"
          class="btn btn-sm btn-primary my-2 my-sm-0 ephemeral-button"
          data-submit-button
          @click.prevent="handleSubmit"
        >
          <span>Submit</span>
          <img
            v-if="isSubmitLoading"
            src="https://samherbert.net/svg-loaders/svg-loaders/tail-spin.svg"
            height="16"
          />
        </button>
      </form>
    </div>
    <div ref="layout-root" class="col px-0" style="min-height: 60em"></div>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Ref, Watch } from 'vue-property-decorator';
import Vue, { CreateElement } from 'vue';
import type { Component as VueComponent } from 'vue'; // this is the component type for Vue components
import GoldenLayout from 'golden-layout';

import { types } from '../api_types';

import store from './GraderStore';
import * as Util from './util';

interface GraderComponent extends Vue {
  title?: string;
  onResize?: () => void;
}
interface ComponentProps {
  [key: string]: any;
}
interface ComponentState {
  [key: string]: any;
}

@Component
export default class Ephemeral extends Vue {
  @Prop({ default: true }) isEmbedded!: boolean;
  @Prop({ default: 'vs' }) theme!: string;

  @Prop() problem!: types.ProblemInfo;
  @Prop({ default: 'cpp17-gcc' }) initialLanguage!: string;
  @Prop({ default: '' }) initialSource!: string;
  @Prop({ default: () => [] }) acceptedLanguages!: string[];
  @Prop({ default: false }) canSubmit!: boolean;
  @Prop({ default: true }) canRun!: boolean;

  @Ref('layout-root') readonly layoutRoot!: HTMLDivElement;

  goldenLayout: GoldenLayout | null = null;
  componentMapping: { [key: string]: VueComponent } = {};

  get isUploadButton() {
    return !this.isEmbedded;
  }
  get isDownloadButton() {
    return !this.isEmbedded;
  }
  get isSubmitButton() {
    return store.getters['showSubmitButton'];
  }
  get isRunButton() {
    // TODO: use showRunButton getter from store
    return true;
  }

  get selectedLanguage() {
    return store.getters['request.language'];
  }
  set selectedLanguage(language: string) {
    // TODO: dispatch request.langauge action
  }
  get languages(): string[] {
    return store.getters['languages'];
  }

  getLanguageName(language: string): string {
    return Util.supportedLanguages[language].name;
  }
  initProblem() {
    // TODO: trigger INIT_PROBLEM mutation
  }
  @Watch('problem', { deep: true })
  onProblemChange() {
    this.initProblem();
  }
  beforeMount() {
    this.initProblem();
  }
  mounted() {
    // TODO: init golden layout
  }

  onDetailsJsonReady() {
    // TODO: implement this over from ephemeral.ts
  }
  onFilesZipReady() {
    // TODO: implement this over from ephemeral.ts
  }
  handleSubmit() {
    // TODO: implement this over from ephemeral.ts
  }
  handleRun() {
    // TODO: implement this over from ephemeral.ts
  }

  RegisterVueComponent(componentName: string, component: VueComponent) {
    // eslint-disable-next-line @typescript-eslint/no-this-alias
    const self = this;

    this.goldenLayout?.registerComponent(
      componentName,
      // cannot use an arrow function because
      // it causes a "ComponentConstructor is not a constructor" error
      function (
        container: GoldenLayout.Container,
        componentState: ComponentState,
      ) {
        container.on('open', () => {
          const props: ComponentProps = {
            storeMapping: componentState.storeMapping,
            theme: self.theme,
          };
          for (const k in componentState) {
            if (k === 'id' || !componentState[k]) continue;
            props[k] = componentState[k];
          }

          const vue = new Vue({
            el: container.getElement()[0],
            components: {
              [componentName]: component,
            },
            render: function (createElement: CreateElement) {
              return createElement(componentName, {
                props: props,
              });
            },
          });

          const vueComponent: GraderComponent = vue.$children[0];
          if (vueComponent.title) {
            container.setTitle(vueComponent.title);
            vueComponent.$watch('title', function (title: string) {
              container.setTitle(title);
            });
          }
          if (vueComponent.onResize) {
            container.on('resize', () => vueComponent.onResize?.());
          }
        });

        self.componentMapping[componentState.id] = component;
      },
    );
  }
}
</script>

<style scoped>
@import url('https://golden-layout.com/assets/css/goldenlayout-base.css');
@import url('https://golden-layout.com/assets/css/goldenlayout-light-theme.css');
</style>
