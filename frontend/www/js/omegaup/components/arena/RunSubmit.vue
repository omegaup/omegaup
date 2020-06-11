<template>
  <form class="run-submit-view" v-on:submit.prevent="onSubmit">
    <div class="close-container">
      <button class="close">❌</button>
    </div>
    <div class="form-group row">
      <label class="col-sm-2 col-form-label">
        {{ T.wordsLanguage }}
      </label>
      <div class="col-sm-4">
        <select class="form-control" name="language" v-model="selectedLanguage">
          <option v-bind:value="key" v-for="(language, key) in languages">{{
            language
          }}</option>
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
        <input
          type="submit"
          class="btn btn-primary"
          v-bind:disabled="secondsRemaining > 0"
          v-bind:value="buttonDescription"
        />
      </div>
    </div>
  </form>
</template>

<style lang="scss">
@import '../../../../sass/main.scss';
.CodeMirror pre.CodeMirror-line {
  padding: 0px 35px;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Ref, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import * as ui from '../../ui';
import T from '../../lang';
import arena_CodeView from './CodeView.vue';

@Component({
  components: {
    'omegaup-arena-code-view': arena_CodeView,
  },
})
export default class ArenaRunSubmit extends Vue {
  @Ref() inputFile!: HTMLInputElement;
  @Prop() languages!: omegaup.Languages;
  @Prop({ default: 0 }) submissionGapSecondsRemaining!: number;
  @Prop() inputLimit!: number;
  @Prop() preferredLanguage!: string;

  T = T;
  selectedLanguage = '';
  code = '';
  secondsRemaining = this.submissionGapSecondsRemaining;
  submissionGapInterval: number = 0;

  get buttonDescription(): string {
    if (this.secondsRemaining < 1) {
      return T.wordsSend;
    }

    return ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
      submissionGap: this.secondsRemaining,
    });
  }

  get filename(): string {
    return `Main${this.extension}`;
  }

  get extension(): string {
    if (!this.selectedLanguage) {
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
    if (this.selectedLanguage !== 'cat') {
      return `.${this.selectedLanguage}`;
    }
    return '';
  }

  @Watch('preferredLanguage')
  onPreferredLanguageChanged(newValue: string): void {
    this.selectedLanguage = newValue;
  }

  @Watch('submissionGapSecondsRemaining')
  onSubmissionGapChanged(newValue: number): void {
    this.secondsRemaining = this.submissionGapSecondsRemaining;
    if (this.secondsRemaining < 1) return;
    if (this.submissionGapInterval) {
      window.clearInterval(this.submissionGapInterval);
    }
    this.submissionGapInterval = window.setInterval(() => {
      if (this.secondsRemaining < 1) {
        window.clearInterval(this.submissionGapInterval);
      }
      this.secondsRemaining--;
    }, 1000);
  }

  onSubmit(ev: Event): void {
    if (this.secondsRemaining > 0) {
      alert(
        ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
          submissionGap: this.secondsRemaining,
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

      reader.onload = e => {
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
    this.clearInput();
    this.code = '';
  }

  clearInput(): void {
    this.inputFile.type = 'text';
    this.inputFile.type = 'file';
  }
}
</script>
