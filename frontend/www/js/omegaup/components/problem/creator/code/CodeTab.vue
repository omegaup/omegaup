<template>
  <div class="card code-edit">
    <div class="card-body">
      <div class="row">
        <label class="col-sm-2 col-form-label">
          {{ T.wordsLanguage }}
        </label>
        <div class="col-sm-4">
          <select
            v-model="selectedLanguage"
            class="form-control"
            name="language"
          >
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
              :language="selectedLanguage"
              :readonly="false"
              @change-language="handleChangeLanguage($event)"
            ></omegaup-creator-code-view>
          </div>
        </div>
      </div>
      <div class="form-group row mt-3 align-items-center">
        <label class="col-sm-3 col-form-label">
          {{ T.problemCreatorCodeUpload }}
        </label>
        <div class="col-sm-7">
          <input
            class="w-100"
            type="file"
            name="file"
            @change="handleInputFile"
          />
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <button class="btn btn-primary" type="submit" @click="updateCode">
            {{ T.problemCreatorCodeSave }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Watch } from 'vue-property-decorator';
import { omegaup } from '../../../../omegaup';
import * as ui from '../../../../ui';
import T from '../../../../lang';
import creator_CodeView from '../../../arena/CodeView.vue';
import { LanguageInfo, supportedLanguages } from '../../../../grader/util';

@Component({
  components: {
    'omegaup-creator-code-view': creator_CodeView,
  },
})
export default class CodeTab extends Vue {
  inputLimit = 512 * 1024; // Hardcoded as 512kiB _must_ be enough for anybody.
  T = T;
  ui = ui;
  omegaup = omegaup;
  selectedLanguage = '';
  code = '';
  extension = '';

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
    ui.success(T.problemCreatorUpdateAlert);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../../../sass/main.scss';

.code-edit {
  background: var(--creator-code-background-color);
}
</style>
