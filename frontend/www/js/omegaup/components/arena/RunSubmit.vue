<template>
  <form data-run-submit v-on:submit.prevent="onSubmit">
    <div class="close-container">
      <button type="button" class="close">❌</button>
    </div>
    <div class="form-group row">
      <label class="col-sm-2 col-form-label">
        {{ T.wordsLanguage }}
      </label>
      <div class="col-sm-4">
        <select class="form-control" name="language" v-model="selectedLanguage">
          <option
            v-bind:value="key"
            v-for="(language, key) in allowedLanguages"
            >{{ language }}</option
          >
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
      <label class="col-sm-7 col-form-label">{{ T.arenaRunSubmitPaste }}</label>
    </div>
    <div class="code-view form-group">
      <omegaup-arena-code-view
        v-bind:language="selectedLanguage"
        v-bind:readonly="false"
        v-model="code"
      ></omegaup-arena-code-view>
    </div>
    <div class="form-group row">
      <label class="col-sm-3 col-form-label">
        {{ T.arenaRunSubmitUpload }}
      </label>
      <div class="col-sm-7">
        <input type="file" name="file" ref="inputFile" />
      </div>
    </div>
    <div class="form-group row">
      <div class="col-sm-10">
        <button
          type="submit"
          class="btn btn-primary"
          v-bind:disabled="!canSubmit"
        >
          <omegaup-countdown
            v-if="!canSubmit"
            v-bind:target-time="nextSubmissionTimestamp"
            v-bind:countdown-format="
              omegaup.CountdownFormat.WaitBetweenUploadsSeconds
            "
            v-on:emit-finish="now = Date.now()"
          ></omegaup-countdown>
          <span v-else="">{{ T.wordsSend }}</span>
        </button>
      </div>
    </div>
  </form>
</template>

<style lang="scss">
@import '../../../../sass/main.scss';
.CodeMirror pre.CodeMirror-line {
  padding: 0px 35px;
}

form {
  background: #eee;
  width: 80%;
  height: 90%;
  margin: auto;
  border: 2px solid #ccc;
  padding: 1em;
  position: absolute;
  overflow-y: auto;
  overflow-x: hidden;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  flex-direction: column;
  .close-container {
    width: 100%;
    .close {
      position: absolute;
      top: 0;
      right: 0;
      background-color: $omegaup-white;
      border: 1px solid #ccc;
      border-width: 0 0 1px 1px;
      font-size: 110%;
      width: 25px;
      height: 25px;
      &:hover {
        background-color: #eee;
      }
    }
  }
  .languages {
    width: 100%;
  }
  .filename-extension {
    width: 100%;
  }
  .run-submit-paste-text {
    width: 100%;
  }
  .code-view {
    width: 100%;
    flex-grow: 1;
    overflow: auto;
  }
  .upload-file {
    width: 100%;
  }
  .submit-run {
    width: 100%;
  }
}

input[type='submit'] {
  font-size: 110%;
  padding: 0.3em 0.5em;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Ref, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import * as ui from '../../ui';
import T from '../../lang';
import arena_CodeView from './CodeView.vue';
import omegaup_Countdown from '../Countdown.vue';

@Component({
  components: {
    'omegaup-arena-code-view': arena_CodeView,
    'omegaup-countdown': omegaup_Countdown,
  },
})
export default class ArenaRunSubmit extends Vue {
  @Ref() inputFile!: HTMLInputElement;
  @Prop() languages!: string[];
  @Prop({ default: () => new Date() }) nextSubmissionTimestamp!: Date;
  @Prop() inputLimit!: number;
  @Prop() preferredLanguage!: string;

  T = T;
  omegaup = omegaup;
  selectedLanguage = '';
  code = '';
  now: number = Date.now();

  get canSubmit(): boolean {
    return this.nextSubmissionTimestamp.getTime() < this.now;
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
      { language: 'c', name: 'C11 (gcc 9.3)' },
      { language: 'c11-gcc', name: 'C11 (gcc 9.3)' },
      { language: 'c11-clang', name: 'C11 (clang 10.0)' },
      { language: 'cpp', name: 'C++03 (g++ 9.3)' },
      { language: 'cpp11', name: 'C++11 (g++ 9.3)' },
      { language: 'cpp11-gcc', name: 'C++11 (g++ 9.3)' },
      { language: 'cpp11-clang', name: 'C++11 (clang++ 10.0)' },
      { language: 'cpp17-gcc', name: 'C++17 (g++ 9.3)' },
      { language: 'cpp17-clang', name: 'C++17 (clang++ 10.0)' },
      { language: 'java', name: 'Java (openjdk 14.0)' },
      { language: 'py', name: 'Python 2.7' },
      { language: 'py2', name: 'Python 2.7' },
      { language: 'py3', name: 'Python 3.8' },
      { language: 'rb', name: 'Ruby (2.7)' },
      { language: 'cs', name: 'C# (8.0, dotnet 3.1)' },
      { language: 'pas', name: 'Pascal (fpc 3.0)' },
      { language: 'cat', name: 'Output Only' },
      { language: 'hs', name: 'Haskell (ghc 8.6)' },
      { language: 'lua', name: 'Lua (5.3)' },
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

  onSubmit(ev: Event): void {
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
    const file = this.inputFile.files![0];
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
      } else {
        // 100kB _must_ be enough for anybody.
        if (file.size >= 100 * 1024) {
          alert(ui.formatString(T.arenaRunSubmitFilesize, { limit: '100kB' }));
          return;
        }
        reader.readAsDataURL(file);
      }

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
