<template>
  <omegaup-overlay-popup @dismiss="$emit('dismiss')">
    <form
      data-run-submit
      class="d-flex flex-column h-100"
      @submit.prevent="onSubmit"
    >
      <div class="form-group row introjs-language">
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
      <div class="h-100 introjs-pasteyourcode">
        <omegaup-arena-code-view
          v-model="code"
          :language="selectedLanguage"
          :readonly="false"
        ></omegaup-arena-code-view>
      </div>
      <div class="form-group row mt-3 align-items-center introjs-upload-file">
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
            class="btn btn-primary introjs-submit"
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
import 'intro.js/introjs.css';
import introJs from 'intro.js';
import VueCookies from 'vue-cookies';
Vue.use(VueCookies, { expire: -1 });

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
  @Prop() hasVisitedSectionPopup!: boolean;
  @Prop() hasVisitedSection!: boolean;

  T = T;
  omegaup = omegaup;
  selectedLanguage = this.preferredLanguage;
  code = '';
  now: number = Date.now();

  mounted() {
    const title = T.interactiveGuideUploadSolutionTitle;
    if (!this.hasVisitedSectionPopup) {
      introJs()
        .setOptions({
          nextLabel: T.interactiveGuideNextButton,
          prevLabel: T.interactiveGuidePreviousButton,
          doneLabel: T.interactiveGuideDoneButton,
          steps: [
            {
              element: document.querySelector('.introjs-language') as Element,
              title,
              intro: T.interactiveGuideUploadSolutionLanguage,
            },
            {
              element: document.querySelector(
                '.introjs-pasteyourcode',
              ) as Element,
              title,
              intro: T.interactiveGuideUploadSolutionPasteYourCode,
            },
            {
              element: document.querySelector(
                '.introjs-upload-file',
              ) as Element,
              title,
              intro: T.interactiveGuideUploadSolutionUploadFile,
            },
            {
              element: document.querySelector('.introjs-submit') as Element,
              title,
              intro: T.interactiveGuideUploadSolutionSubmitButton,
            },
          ],
        })
        .start();
      this.$cookies.set('has-visited-submit-popup', true, -1);
    }
  }

  get canSubmit(): boolean {
    return this.nextSubmissionTimestamp.getTime() <= this.now;
  }

  get filename(): string {
    return `Main${this.extension}`;
  }

  get allowedLanguages(): omegaup.Languages {
    let allowedLanguages: omegaup.Languages = {};
    const allLanguages = [
      { language: '', name: '' },
      { language: 'kp', name: 'Karel (Pascal)' },
      { language: 'kj', name: 'Karel (Java)' },
      { language: 'c', name: 'C11 (gcc 10.3)' },
      { language: 'c11-gcc', name: 'C11 (gcc 10.3)' },
      { language: 'c11-clang', name: 'C11 (clang 10.0)' },
      { language: 'cpp', name: 'C++03 (g++ 10.3)' },
      { language: 'cpp11', name: 'C++11 (g++ 10.3)' },
      { language: 'cpp11-gcc', name: 'C++11 (g++ 10.3)' },
      { language: 'cpp11-clang', name: 'C++11 (clang++ 10.0)' },
      { language: 'cpp17-gcc', name: 'C++17 (g++ 10.3)' },
      { language: 'cpp17-clang', name: 'C++17 (clang++ 10.0)' },
      { language: 'cpp20-gcc', name: 'C++20 (g++ 10.3)' },
      { language: 'cpp20-clang', name: 'C++20 (clang++ 10.0)' },
      { language: 'java', name: 'Java (openjdk 16.0)' },
      { language: 'kt', name: 'Kotlin (1.6.10)' },
      { language: 'py', name: 'Python (2.7)' },
      { language: 'py2', name: 'Python (2.7)' },
      { language: 'py3', name: 'Python (3.9)' },
      { language: 'rb', name: 'Ruby (2.7)' },
      { language: 'cs', name: 'C# (10, dotnet 6.0)' },
      { language: 'pas', name: 'Pascal (fpc 3.0)' },
      { language: 'cat', name: T.outputOnly },
      { language: 'hs', name: 'Haskell (ghc 8.8)' },
      { language: 'lua', name: 'Lua (5.3)' },
      { language: 'go', name: 'Go (1.18.beta2)' },
      { language: 'rs', name: 'Rust (1.56.1)' },
      { language: 'js', name: 'JavaScript (Node.js 16)' },
    ];

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
  onPreferredLanguageChanged(newValue: string): void {
    this.selectedLanguage = newValue;
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

      const validExtensions = [
        'cpp',
        'c',
        'cs',
        'java',
        'txt',
        'hs',
        'kp',
        'kj',
        'p',
        'pas',
        'py',
        'rb',
        'lua',
      ];

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
