<template>
  <omegaup-overlay-popup
    class="run-submit-popup overlay-popup--wide"
    @dismiss="$emit('dismiss')"
  >
    <form
      data-run-submit
      class="modal-form d-flex flex-column h-100"
      @submit.prevent="onSubmit"
    >
      <div class="form-group row">
        <label class="col-sm-2 col-form-label">
          {{ T.wordsLanguage }}
        </label>
        <div class="col-sm-4">
          <select
            v-model="selectedLanguage"
            class="form-control modal-form__control"
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
      <div class="h-100 run-submit-code-view">
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
          <input
            ref="inputFile"
            class="w-100 modal-form__control"
            type="file"
            name="file"
          />
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
import { defineComponent, PropType } from 'vue';
import { omegaup } from '../../omegaup';
import * as ui from '../../ui';
import T from '../../lang';
import { SafeStorage } from '../../safe_storage';
import arena_CodeView from './CodeView.vue';
import omegaup_Countdown from '../Countdown.vue';
import omegaup_OverlayPopup from '../OverlayPopup.vue';
import {
  LanguageInfo,
  supportedExtensions,
  supportedLanguages,
} from '../../grader/util';
import { sourceTemplates } from '../../grader/GraderTemplates';
import { getCodeTemplate } from '../../code_templates';

export default defineComponent({
  name: 'ArenaRunSubmitPopup',
  components: {
    'omegaup-arena-code-view': arena_CodeView,
    'omegaup-countdown': omegaup_Countdown,
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
  props: {
    languages: {
      type: Array as PropType<string[]>,
      required: true,
    },
    nextSubmissionTimestamp: {
      type: Date,
      required: true,
    },
    inputLimit: {
      type: Number,
      default: 0,
    },
    preferredLanguage: {
      type: String as PropType<null | string>,
      default: null,
    },
  },
  emits: ['dismiss', 'submit-run'],
  data() {
    return {
      T,
      omegaup,
      selectedLanguage: this.preferredLanguage as null | string,
      code: '',
      now: Date.now(),
    };
  },
  computed: {
    canSubmit(): boolean {
      return this.nextSubmissionTimestamp.getTime() <= this.now;
    },
    filename(): string {
      return `Main${this.extension}`;
    },
    allowedLanguages(): omegaup.Languages {
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
    },
    extension(): string {
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
    },
  },
  watch: {
    selectedLanguage: {
      immediate: true,
      handler(
        newLanguage: null | string,
        oldLanguage: null | string | undefined,
      ): void {
        if (!newLanguage || newLanguage === oldLanguage) return;
        this.loadBoilerplateForLanguage(newLanguage);
        if (oldLanguage !== undefined) {
          SafeStorage.setItem('arena:selectedLanguage', newLanguage);
        }
      },
    },
    preferredLanguage(): void {
      this.revalidateLanguage();
    },
    languages(): void {
      this.revalidateLanguage();
    },
  },
  mounted(): void {
    const savedLanguage = SafeStorage.getItem('arena:selectedLanguage');
    if (savedLanguage && savedLanguage in this.allowedLanguages) {
      this.selectedLanguage = savedLanguage;
    }
  },
  methods: {
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
    },
    loadBoilerplateForLanguage(language: string): void {
      if (!language || language === 'cat') {
        this.code = '';
        return;
      }
      const extension = this.getLanguageExtension(language);
      if (!extension) {
        this.code = '';
        return;
      }
      const customTemplate = getCodeTemplate(extension);
      if (customTemplate !== null) {
        this.code = customTemplate;
        return;
      }
      this.code = sourceTemplates[extension] ?? '';
    },
    handleChangeLanguage(language: string): void {
      this.selectedLanguage = language;
    },
    revalidateLanguage(): void {
      if (
        this.selectedLanguage &&
        this.selectedLanguage in this.allowedLanguages
      ) {
        return;
      }
      if (
        this.preferredLanguage &&
        this.preferredLanguage in this.allowedLanguages
      ) {
        this.selectedLanguage = this.preferredLanguage;
        return;
      }
      this.selectedLanguage = Object.keys(this.allowedLanguages)[0] || null;
    },
    onSubmit(): void {
      if (!this.canSubmit) {
        ui.warning(
          ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
            submissionGap: Math.ceil(
              (this.nextSubmissionTimestamp.getTime() - Date.now()) / 1000,
            ),
          }),
        );
        return;
      }

      if (!this.selectedLanguage) {
        ui.error(T.arenaRunSubmitMissingLanguage);
        return;
      }
      const inputFile = this.$refs.inputFile as HTMLInputElement;
      const file = inputFile?.files?.[0];
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
            ui.error(
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
          ui.error(
            ui.formatString(T.arenaRunSubmitFilesize, { limit: '512kiB' }),
          );
          return;
        }
        reader.readAsDataURL(file);

        return;
      }

      if (!this.code) {
        ui.error(T.arenaRunSubmitEmptyCode);
        return;
      }

      this.$emit('submit-run', this.code, this.selectedLanguage);
    },
    clearForm(): void {
      this.code = '';
      const inputFile = this.$refs.inputFile as HTMLInputElement;
      if (inputFile) {
        inputFile.type = 'text';
        inputFile.type = 'file';
      }
    },
  },
});
</script>

<style lang="scss">
@media (max-height: 706px), (max-width: 475px) {
  .run-submit-popup form[data-run-submit] {
    height: calc(90vh - 54px) !important;
    max-height: none;
    min-height: 0;
    overflow: visible !important;
  }

  .run-submit-popup form[data-run-submit] > * {
    flex-shrink: 0;
  }

  .run-submit-popup form[data-run-submit] > .run-submit-code-view {
    height: auto !important;
    flex: 1 1 0;
    min-height: 100px;
    flex-shrink: 1;
  }

  .run-submit-popup .run-submit-code-view .CodeMirror-scroll {
    height: 100% !important;
  }
}
</style>
