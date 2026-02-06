<template>
  <omegaup-overlay-popup @dismiss="$emit('dismiss')">
    <form
      ref="submitForm"
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
            ref="languageSelector"
            v-model="selectedLanguage"
            class="form-control introjs-language-selector"
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
      <div ref="codeEditorWrapper" class="h-100 introjs-code-editor">
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
            class="w-100 introjs-file-upload"
            type="file"
            name="file"
          />
        </div>
      </div>
      <div class="form-group row">
        <div class="col-sm-10">
          <button
            ref="submitButton"
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
import introJs from 'intro.js';
import 'intro.js/introjs.css';
@Component({
  components: {
    'omegaup-arena-code-view': arena_CodeView,
    'omegaup-countdown': omegaup_Countdown,
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
})
export default class ArenaRunSubmitPopup extends Vue {
  @Ref() inputFile!: HTMLInputElement;
  @Ref() submitForm!: HTMLFormElement;
  @Ref() languageSelector!: HTMLSelectElement;
  @Ref() codeEditorWrapper!: HTMLDivElement;
  @Ref() submitButton!: HTMLButtonElement;
  @Prop() languages!: string[];
  @Prop({ required: true }) nextSubmissionTimestamp!: Date;
  @Prop() inputLimit!: number;
  @Prop({ default: null }) preferredLanguage!: null | string;

  T = T;
  omegaup = omegaup;
  selectedLanguage = this.preferredLanguage;
  code = '';
  now: number = Date.now();
  private introJsInstance: introJs.IntroJs | null = null;

  handleChangeLanguage(language: string): void {
    this.selectedLanguage = language;
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

  private hasTutorialBeenDismissed(): boolean {
    try {
      return (
        localStorage.getItem('omegaup.submission.tutorial.dismissed') === 'true'
      );
    } catch (e) {
      // Private browsing mode or localStorage disabled
      return false;
    }
  }

  private markTutorialAsDismissed(): void {
    try {
      localStorage.setItem('omegaup.submission.tutorial.dismissed', 'true');
    } catch (e) {
      // Private browsing mode or localStorage disabled
    }
  }

  private isElementVisible(element: Element | null): boolean {
    if (!element) {
      return false;
    }
    const rect = element.getBoundingClientRect();
    return (
      rect.width > 0 &&
      rect.height > 0 &&
      rect.top >= 0 &&
      rect.left >= 0 &&
      rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  }

  private launchTutorial(): void {
    if (this.hasTutorialBeenDismissed() || this.introJsInstance !== null) {
      return;
    }

    // Wait for DOM and async editor mount - use multiple attempts
    const attemptLaunch = (attempt: number, maxAttempts: number = 15): void => {
      if (this.hasTutorialBeenDismissed() || this.introJsInstance !== null) {
        return;
      }

      // Try to get elements using refs first (most reliable)
      let languageSelectorEl: Element | null = this.languageSelector || null;
      let codeEditorEl: Element | null = this.codeEditorWrapper || null;
      let fileUploadEl: Element | null = this.inputFile || null;
      let submitButtonEl: Element | null = this.submitButton || null;

      // If refs are not available, try querySelector within form
      if (!languageSelectorEl && this.submitForm) {
        languageSelectorEl = this.submitForm.querySelector(
          '.introjs-language-selector',
        );
      }
      if (!codeEditorEl && this.submitForm) {
        codeEditorEl =
          this.submitForm.querySelector('.introjs-code-editor') ||
          this.submitForm.querySelector('[data-code-mirror]');
      }
      if (!fileUploadEl && this.submitForm) {
        fileUploadEl = this.submitForm.querySelector('.introjs-file-upload');
      }
      if (!submitButtonEl && this.submitForm) {
        submitButtonEl = this.submitForm.querySelector('[data-submit-run]');
      }

      // Final fallback: document-wide search (for overlay popups in portal)
      // Search within overlay popup container first
      const overlayPopup = document.querySelector('[data-overlay-popup]');
      if (overlayPopup) {
        if (!languageSelectorEl) {
          languageSelectorEl = overlayPopup.querySelector(
            '.introjs-language-selector',
          );
        }
        if (!codeEditorEl) {
          codeEditorEl =
            overlayPopup.querySelector('.introjs-code-editor') ||
            overlayPopup.querySelector('[data-code-mirror]');
        }
        if (!fileUploadEl) {
          fileUploadEl = overlayPopup.querySelector('.introjs-file-upload');
        }
        if (!submitButtonEl) {
          submitButtonEl = overlayPopup.querySelector('[data-submit-run]');
        }
      }

      // Last resort: document-wide search
      if (!languageSelectorEl) {
        languageSelectorEl = document.querySelector(
          '.introjs-language-selector',
        );
      }
      if (!codeEditorEl) {
        codeEditorEl =
          document.querySelector('.introjs-code-editor') ||
          document.querySelector('[data-code-mirror]');
      }
      if (!fileUploadEl) {
        fileUploadEl = document.querySelector('.introjs-file-upload');
      }
      if (!submitButtonEl) {
        submitButtonEl = document.querySelector('[data-submit-run]');
      }

      // Check if overlay popup is visible
      const overlayVisible =
        overlayPopup &&
        this.isElementVisible(overlayPopup) &&
        window.getComputedStyle(overlayPopup).display !== 'none';

      // If all elements are found and visible, or we've exhausted attempts, proceed
      if (
        (languageSelectorEl &&
          codeEditorEl &&
          fileUploadEl &&
          submitButtonEl &&
          overlayVisible) ||
        attempt >= maxAttempts
      ) {
        const steps: introJs.Step[] = [
          {
            title: T.arenaRunSubmitTutorialTitle,
            intro: T.arenaRunSubmitTutorialWelcome,
          },
          {
            element: languageSelectorEl || undefined,
            title: T.arenaRunSubmitTutorialLanguageTitle,
            intro: T.arenaRunSubmitTutorialLanguageIntro,
          },
          {
            element: codeEditorEl || undefined,
            title: T.arenaRunSubmitTutorialEditorTitle,
            intro: T.arenaRunSubmitTutorialEditorIntro,
          },
          {
            element: fileUploadEl || undefined,
            title: T.arenaRunSubmitTutorialUploadTitle,
            intro: T.arenaRunSubmitTutorialUploadIntro,
          },
          {
            element: submitButtonEl || undefined,
            title: T.arenaRunSubmitTutorialSubmitTitle,
            intro: T.arenaRunSubmitTutorialSubmitIntro,
          },
        ];

        // Filter out steps if target DOM element is missing
        const validSteps = steps.filter((step) => {
          if (!step.element) {
            return true; // Keep steps without element (welcome step)
          }
          return step.element !== null;
        });

        if (validSteps.length <= 1) {
          // Only welcome step, skip tutorial
          return;
        }

        this.introJsInstance = introJs()
          .setOptions({
            nextLabel: T.interactiveGuideNextButton,
            prevLabel: T.interactiveGuidePreviousButton,
            doneLabel: T.interactiveGuideDoneButton,
            steps: validSteps,
            exitOnOverlayClick: true,
            exitOnEsc: true,
          })
          .onexit(() => {
            this.markTutorialAsDismissed();
            this.introJsInstance = null;
          })
          .oncomplete(() => {
            this.markTutorialAsDismissed();
            this.introJsInstance = null;
          })
          .start();
      } else {
        // Retry after a short delay
        setTimeout(() => {
          attemptLaunch(attempt + 1, maxAttempts);
        }, 300);
      }
    };

    // Wait for overlay to be visible and DOM to be ready
    this.$nextTick(() => {
      // Additional delay to ensure overlay popup is fully rendered
      setTimeout(() => {
        attemptLaunch(1);
      }, 500);
    });
  }

  mounted(): void {
    this.launchTutorial();
  }

  beforeDestroy(): void {
    if (this.introJsInstance !== null) {
      this.introJsInstance.exit();
      this.introJsInstance = null;
    }
  }
}
</script>
