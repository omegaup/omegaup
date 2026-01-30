<template>
  <div class="card code-edit">
    <div class="card-body">
      <div class="form-group row align-items-center">
        <label class="col-12 col-sm-auto col-form-label mb-2 mb-sm-0 pr-sm-2">
          {{ T.wordsLanguage }}
        </label>
        <div class="col-12 col-sm-auto pl-sm-0">
          <select
            v-model="selectedLanguage"
            data-problem-creator-code-language
            class="form-control"
            name="language"
          >
            <option value="" disabled>
              {{ T.problemCreatorSelectLanguage }}
            </option>
            <option
              v-for="(language, key) in allowedLanguages"
              :key="key"
              :value="key"
            >
              {{ language }}
            </option>
          </select>
        </div>
      </div>
      <br />
      <div class="row">
        <div class="col-md-12">
          <div class="h-100">
            <omegaup-creator-code-view
              v-model="code"
              data-problem-creator-code-editor
              :language="selectedLanguage"
              :readonly="false"
              @change-language="handleChangeLanguage($event)"
            ></omegaup-creator-code-view>
          </div>
        </div>
      </div>
      <div class="form-group row mt-3 align-items-center">
        <label class="col-12 col-sm-auto col-form-label mb-2 mb-sm-0 pr-sm-2">
          {{ T.problemCreatorCodeUpload }}
        </label>
        <div
          class="col-12 col-sm-auto pl-sm-0 d-flex align-items-center overflow-hidden"
        >
          <input
            data-problem-creator-code-input
            class="text-truncate mw-100"
            type="file"
            name="file"
            @change="handleInputFile"
          />
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <button
            data-problem-creator-code-save-btn
            class="btn btn-primary .intro-js-code"
            type="submit"
            @click="updateCode"
          >
            {{ T.problemCreatorCodeSave }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../../../omegaup';
import * as ui from '../../../../ui';
import T from '../../../../lang';
import creator_CodeView from '../../../arena/CodeView.vue';
import { LanguageInfo, supportedLanguages } from '../../../../grader/util';
import introJs from 'intro.js';
import 'intro.js/introjs.css';
import VueCookies from 'vue-cookies';
import { TabIndex } from '../Tabs.vue';

Vue.use(VueCookies, { expire: -1 });

@Component({
  components: {
    'omegaup-creator-code-view': creator_CodeView,
  },
})
export default class CodeTab extends Vue {
  @Prop({ default: T.problemCreatorEmpty }) codeProp!: string;
  @Prop({ default: T.problemCreatorEmpty }) extensionProp!: string;
  @Prop() activeTabIndex!: TabIndex;

  inputLimit = 512 * 1024; // Hardcoded as 512kiB _must_ be enough for anybody.
  T = T;
  ui = ui;
  omegaup = omegaup;
  selectedLanguage = '';
  codeInternal = T.problemCreatorEmpty;
  extensionInternal = T.problemCreatorEmpty;

  get code(): string {
    return this.codeInternal;
  }
  set code(newCode: string) {
    this.codeInternal = newCode;
  }

  get extension(): string {
    return this.extensionInternal;
  }
  set extension(newExtension: string) {
    this.extensionInternal = newExtension;
  }

  @Watch('codeProp')
  onCodePropChanged() {
    this.code = this.codeProp;
  }

  @Watch('extensionProp')
  onextensionPropChanged() {
    if (
      this.extensionProp &&
      this.allowedExtensions.includes(this.extensionProp)
    ) {
      const languageInfo = Object.values(supportedLanguages).find(
        (language) => language.extension === this.extensionProp,
      );
      if (languageInfo) {
        this.selectedLanguage = languageInfo.language;
      }
    }
  }

  @Watch('activeTabIndex')
  onActiveTabIndexChanged(newIndex: TabIndex) {
    if (newIndex === TabIndex.Code) {
      this.startIntroGuide();
    }
  }

  get allowedLanguages(): omegaup.Languages {
    let allowedLanguages: omegaup.Languages = {};
    Object.values(supportedLanguages).forEach((languageInfo: LanguageInfo) => {
      allowedLanguages[languageInfo.language] = languageInfo.name;
    });
    return allowedLanguages;
  }

  get allowedExtensions(): string[] {
    let allowedExtensions: string[] = [];
    Object.values(supportedLanguages).forEach((languageInfo: LanguageInfo) => {
      allowedExtensions.push(languageInfo.extension);
    });
    return allowedExtensions;
  }

  @Watch('selectedLanguage')
  onSelectedLanguageChanged() {
    const languageInfo = Object.values(supportedLanguages).find(
      (language) => language.language === this.selectedLanguage,
    );
    if (languageInfo) {
      this.extension = languageInfo.extension;
    }
  }

  readFile(e: HTMLInputElement): File | null {
    return (e.files && e.files[0]) || null;
  }

  handleInputFile(ev: Event): void {
    const file = this.readFile(ev.target as HTMLInputElement);

    if (file) {
      if (this.inputLimit && file.size >= this.inputLimit) {
        alert(
          ui.formatString(T.problemCreatorCodeUploadFilesize, {
            limit: `${this.inputLimit / 1024} KiB`,
          }),
        );
        return;
      }
      let fileExtension = file.name.split('.').pop()?.toLowerCase();
      if (fileExtension && this.allowedExtensions.includes(fileExtension)) {
        const languageInfo = Object.values(supportedLanguages).find(
          (language) => language.extension === fileExtension,
        );
        if (languageInfo) {
          this.selectedLanguage = languageInfo.language;
        }
      }
      const reader = new FileReader();
      reader.onload = (event) => {
        const result = event.target?.result ?? null;
        if (result) {
          this.code = result.toString();
        }
      };
      reader.readAsText(file);
    }
  }

  handleChangeLanguage(language: string): void {
    this.selectedLanguage = language;
  }

  updateCode() {
    this.$store.commit('updateCodeContent', this.code);
    this.$store.commit('updateCodeExtension', this.extension);
    this.$emit('show-update-success-message');
  }

  startIntroGuide() {
    if (!this.$cookies.get('has-visited-code-tab')) {
      introJs()
        .setOptions({
          nextLabel: T.interactiveGuideNextButton,
          prevLabel: T.interactiveGuidePreviousButton,
          doneLabel: T.interactiveGuideDoneButton,
          steps: [
            {
              title: T.problemCreatorCodeTabIntroSelectLanguageTitle,
              intro: T.problemCreatorCodeTabIntroSelectLanguageIntro,
              element: document.querySelector(
                '[data-problem-creator-code-language]',
              ) as Element,
            },
            {
              title: T.problemCreatorCodeTabIntroWriteCodeTitle,
              intro: T.problemCreatorCodeTabIntroWriteCodeIntro,
              element: document.querySelector(
                '[data-problem-creator-code-editor]',
              ) as Element,
            },
            {
              title: T.problemCreatorCodeTabIntroUploadFileTitle,
              intro: T.problemCreatorCodeTabIntroUploadFileIntro,
              element: document.querySelector(
                '[data-problem-creator-code-input]',
              ) as Element,
            },
            {
              title: T.problemCreatorCodeTabIntroSaveCodeTitle,
              intro: T.problemCreatorCodeTabIntroSaveCodeIntro,
              element: document.querySelector(
                '[data-problem-creator-code-save-btn]',
              ) as Element,
            },
          ],
        })
        .start();

      this.$cookies.set('has-visited-code-tab', true, -1);
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../../../sass/main.scss';

.code-edit {
  background: var(--creator-code-background-color);
}
</style>
