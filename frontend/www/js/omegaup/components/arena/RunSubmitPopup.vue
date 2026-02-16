<template>
  <omegaup-overlay-popup @dismiss="$emit('dismiss')">
    <form
      data-run-submit
      class="d-flex flex-column h-100"
      @submit.prevent="onSubmit"
    >
      <div class="form-group row">
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
      <div class="form-group row">
        <label class="col-sm-7 col-form-label">
          {{ T.arenaRunSubmitFilename }}
          <tt>{{ filename }}</tt>
        </label>
      </div>
      <div class="form-group row">
        <label class="col-sm-7 col-form-label">{{
          T.arenaRunSubmitPaste
        }}</label>
      </div>
      <div class="h-100">
        <omegaup-arena-code-view
          v-model="code"
          :language="selectedLanguage"
          :readonly="false"
          @change-language="handleChangeLanguage($event)"
        ></omegaup-arena-code-view>
      </div>
      <div class="form-group row mt-3 align-items-center">
        <label class="col-sm-3 col-form-label">
          {{ T.arenaRunSubmitUpload }}
        </label>
        <div class="col-sm-7">
          <input ref="inputFile" class="w-100" type="file" name="file" />
        </div>
      </div>
      <div class="form-group row">
        <div class="col-sm-10">
          <button
            type="submit"
            class="btn btn-primary"
            data-submit-run
            :disabled="!canSubmit"
          >
            <omegaup-countdown
              v-if="!canSubmit"
              :target-time="nextSubmissionTimestamp"
              :countdown-format="
                omegaup.CountdownFormat.WaitBetweenUploadsSeconds
              "
              @finish="now = Date.now()"
            ></omegaup-countdown>
            <span v-else>{{ T.wordsSend }}</span>
          </button>
        </div>
      </div>
    </form>
  </omegaup-overlay-popup>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import * as ui from '../../ui';
import T from '../../lang';
import arena_CodeView from './CodeView.vue';
import omegaup_Countdown from '../Countdown.vue';
import omegaup_OverlayPopup from '../OverlayPopup.vue';
import {
  LanguageInfo,
  supportedExtensions,
  supportedLanguages,
} from '../../grader/util';
import { sourceTemplates } from '../../grader/GraderTemplates';
@Component({
  components: {
    'omegaup-arena-code-view': arena_CodeView,
    'omegaup-countdown': omegaup_Countdown,
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
})
export default class ArenaRunSubmitPopup extends Vue {
  @Ref() inputFile!: HTMLInputElement;
  @Prop() languages!: string[];
  @Prop({ required: true }) nextSubmissionTimestamp!: Date;
  @Prop() inputLimit!: number;
  @Prop({ default: null }) preferredLanguage!: null | string;

  T = T;
  omegaup = omegaup;
  selectedLanguage: null | string = this.preferredLanguage;
  code = '';
  now: number = Date.now();

  getLanguageExtension(language: string): string {
    if (!language || language === 'cat') {
      return '';
    }
    const languageInfo = supportedLanguages[language];
    if (languageInfo) {
      return languageInfo.extension;
    }
    // Fallback logic
    if (language.startsWith('cpp')) {
      return 'cpp';
    }
    if (language.startsWith('c11-')) {
      return 'c';
    }
    if (language.startsWith('py')) {
      return 'py';
    }
    return language;
  }

  loadBoilerplateForLanguage(language: string): void {
    if (!language || language === 'cat') {
      this.code = '';
      return;
    }
    const extension = this.getLanguageExtension(language);
    if (extension && sourceTemplates[extension]) {
      this.code = sourceTemplates[extension];
    } else {
      // If no template found, keep current code or set empty
      this.code = '';
    }
  }

  handleChangeLanguage(language: string): void {
    this.selectedLanguage = language;
  }

  @Watch('selectedLanguage', { immediate: true })
  onSelectedLanguageChanged(
    newLanguage: null | string,
    oldLanguage: null | string | undefined,
  ): void {
    if (!newLanguage || newLanguage === oldLanguage) return;
    this.loadBoilerplateForLanguage(newLanguage);
  }

  get canSubmit(): boolean {
    return this.nextSubmissionTimestamp.getTime() <= this.now;
  }

  get filename(): string {
    return `Main${this.extension}`;
  }

  get allowedLanguages(): omegaup.Languages {
    let allowedLanguages: omegaup.Languages = {};
    const allLanguages: { language: string; name: string }[] = Object.values(
      supportedLanguages,
    ).map((languageInfo: LanguageInfo) => ({
      language: languageInfo.language,
      name: languageInfo.name,
    }));
    // dont forget about cat ext
    allLanguages.push({ language: 'cat', name: T.outputOnly });

    allLanguages
      .filter(
        (item) =>
          this.languages.includes(item.language) || item.language === '',
      )
      .forEach((optionItem) => {
        allowedLanguages[optionItem.language] = optionItem.name;
      });
    return allowedLanguages;
  }

  get extension(): string {
    if (!this.selectedLanguage || this.selectedLanguage === 'cat') {
      return '';
    }
    if (this.selectedLanguage.startsWith('cpp')) {
      return '.cpp';
    }
    if (this.selectedLanguage.startsWith('c11-')) {
      return '.c';
    }
    if (this.selectedLanguage.startsWith('py')) {
      return '.py';
    }
    return `.${this.selectedLanguage}`;
  }

  @Watch('preferredLanguage')
  onPreferredLanguageChanged(newValue: null | string): void {
    if (newValue) {
      this.selectedLanguage = newValue;
    }
  }

  onSubmit(): void {
    if (!this.canSubmit) {
      alert(
        ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
          submissionGap: Math.ceil(
            (this.nextSubmissionTimestamp.getTime() - Date.now()) / 1000,
          ),
        }),
      );
      return;
    }

    if (!this.selectedLanguage) {
      alert(T.arenaRunSubmitMissingLanguage);
      return;
    }
    const file = this.inputFile.files?.[0];
    if (file) {
      const reader = new FileReader();

      reader.onload = (e) => {
        const result = e.target?.result ?? null;
        if (result === null) return;
        this.$emit('submit-run', result as string, this.selectedLanguage);
      };

      // add txt, p extensions
      const validExtensions = [...supportedExtensions, 'p', 'txt'];
      if (
        this.selectedLanguage !== 'cat' ||
        file.type.indexOf('text/') === 0 ||
        validExtensions.includes(this.extension)
      ) {
        if (this.inputLimit && file.size >= this.inputLimit) {
          alert(
            ui.formatString(T.arenaRunSubmitFilesize, {
              limit: `${this.inputLimit / 1024} KiB`,
            }),
          );
          return;
        }
        reader.readAsText(file, 'UTF-8');
        return;
      }
      // 512kiB _must_ be enough for anybody.
      if (file.size >= 512 * 1024) {
        alert(ui.formatString(T.arenaRunSubmitFilesize, { limit: '512kiB' }));
        return;
      }
      reader.readAsDataURL(file);

      return;
    }

    if (!this.code) {
      alert(T.arenaRunSubmitEmptyCode);
      return;
    }

    this.$emit('submit-run', this.code, this.selectedLanguage);
  }

  clearForm(): void {
    this.code = '';
    this.inputFile.type = 'text';
    this.inputFile.type = 'file';
  }
}
</script>
