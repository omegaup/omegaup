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
            <span class="fa fa-upload" title="Upload"></span>
          </a>
        </label>

        <label v-if="isDownloadButton" for="download">
          <a class="btn btn-secondary btn-sm mr-sm-2" role="button">
            <span class="fa fa-download" title="Download"></span>
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
          type="button"
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
          type="submit"
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
import Vue from 'vue';
import { types } from '../api_types';
import GoldenLayout from 'golden-layout';

import CaseSelector from './CaseSelector.vue';
import DiffEditor from './DiffEditor.vue';
import IDESettings from './IDESettings.vue';
import MonacoEditor from './MonacoEditor.vue';
import TextEditor from './TextEditor.vue';
import ZipViewer from './ZipViewer.vue';

import store from './GraderStore';
import * as Util from './util';
import { UNEMBEDDED_CONFIG, EMBEDDED_CONFIG } from './GoldenLayoutConfigs';
import {
  TEXT_EDITOR_COMPONENT_NAME,
  MONACO_DIFF_COMPONENT_NAME,
  MONACO_EDITOR_COMPONENT_NAME,
  CASE_SELECTOR_COMPONENT_NAME,
  ZIP_VIEWER_COMPONENT_NAME,
  SETTINGS_COMPONENT_NAME,
} from './GoldenLayoutConfigs';

interface GraderComponent extends Vue {
  title?: string;
  onResize?: () => void;
}

@Component
export default class Ephemeral extends Vue {
  @Prop({ default: false }) isUploadButton!: boolean;
  @Prop({ default: false }) isDownloadButton!: boolean;

  @Prop({ default: true }) isRunButton!: boolean;
  @Prop({ default: false }) isRunLoading!: boolean;

  @Prop({ default: true }) isSubmitButton!: boolean;
  @Prop({ default: false }) isSubmitLoading!: boolean;

  @Prop({ default: true }) isEmbedded!: boolean;
  @Prop({ default: 'vs-dark' }) theme!: string;

  @Prop({ default: () => [] }) acceptedLanguages!: string[];
  @Prop({ default: 'cpp17-gcc' }) initialLanguage!: string;
  @Prop({ default: '' }) alias!: string;
  @Prop({}) settings!: types.ProblemSettingsDistrib;
  @Prop({ default: false }) showSubmitButton!: boolean;

  @Ref('layout-root') readonly layoutRoot!: HTMLDivElement;
  goldenLayout: GoldenLayout | null = null;
  componentMapping: { [key: string]: GraderComponent } = {};

  get selectedLanguage() {
    return store.getters['request.language'];
  }
  set selectedLanguage(language: string) {
    store.commit('request.language', language);
  }
  getLanguageName(language: string): string {
    return Util.supportedLanguages[language].name;
  }
  get languages(): string[] {
    return store.getters['languages'];
  }

  get problemData() {
    return {
      initialLanguage: this.initialLanguage,
      acceptedLanguages: this.acceptedLanguages,
      alias: this.alias,
      settings: this.settings,
    };
  }

  @Watch('problemData', { deep: true })
  onProblemDataChange() {
    this.initProblem();
  }

  initProblem() {
    store.commit('initWithProblem', {
      languages: this.acceptedLanguages,
      alias: this.alias,
      initialLanguage: this.initialLanguage,
      showSubmitButton: this.showSubmitButton,
      settings: this.settings,
    });
  }
  beforeMount() {
    this.initProblem();
  }

  mounted() {
    this.goldenLayout = new GoldenLayout(
      this.isEmbedded ? EMBEDDED_CONFIG : UNEMBEDDED_CONFIG,
      this.layoutRoot,
    );

    // TODO: replace any keyword with more restrictive types
    function RegisterVueComponent(
      layout: any,
      componentName: any,
      component: any,
    ) {
      layout.registerComponent(
        componentName,
        function (container: any, componentState: any) {
          container.on('open', () => {
            let vueComponents: any = {};
            vueComponents[componentName] = component;
            let props: any = {
              storeMapping: componentState.storeMapping,
              theme: 'vs',
            };
            for (let k in componentState) {
              if (k == 'id') continue;
              if (!Object.prototype.hasOwnProperty.call(componentState, k))
                continue;
              props[k] = componentState[k];
            }
            let vue = new Vue({
              el: container.getElement()[0],
              components: vueComponents,
              render: function (createElement: any) {
                return createElement(componentName, {
                  props: props,
                });
              },
            });
            let vueComponent: any = vue.$children[0];
            if (vueComponent.title) {
              container.setTitle(vueComponent.title);
              vueComponent.$watch('title', function (title: any) {
                container.setTitle(title);
              });
            }
            if (vueComponent.onResize) {
              container.on('resize', () => vueComponent.onResize());
            }
          });
        },
      );
    }
    RegisterVueComponent(
      this.goldenLayout,
      CASE_SELECTOR_COMPONENT_NAME,
      CaseSelector,
    );
    RegisterVueComponent(
      this.goldenLayout,
      MONACO_EDITOR_COMPONENT_NAME,
      MonacoEditor,
    );
    RegisterVueComponent(
      this.goldenLayout,
      MONACO_DIFF_COMPONENT_NAME,
      DiffEditor,
    );
    RegisterVueComponent(
      this.goldenLayout,
      SETTINGS_COMPONENT_NAME,
      IDESettings,
    );
    RegisterVueComponent(
      this.goldenLayout,
      TEXT_EDITOR_COMPONENT_NAME,
      TextEditor,
    );
    RegisterVueComponent(
      this.goldenLayout,
      ZIP_VIEWER_COMPONENT_NAME,
      ZipViewer,
    );
    this.goldenLayout.init();
  }
}
</script>

<style scoped>
@import url('https://golden-layout.com/assets/css/goldenlayout-base.css');
@import url('https://golden-layout.com/assets/css/goldenlayout-light-theme.css');
</style>
