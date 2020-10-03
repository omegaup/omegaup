<template>
  <div class="card">
    <div class="card-body">
      <form class="publish-form" v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{ T.contestNewFormAdmissionModeSelect }}</label>
          <a
            data-toggle="tooltip"
            rel="tooltip"
            v-bind:title="T.courseEditAdmissionModeDescription"
          >
            <img src="/media/question.png" />
          </a>
          <select
            v-model="admissionMode"
            class="form-control"
            name="admission-mode"
          >
            <option value="private">
              {{ T.admissionModeManualOnly }}
            </option>
            <option value="registration">
              {{ T.admissionModeShareURL }}
            </option>
            <option v-if="shouldShowPublicOption" value="public">
              {{ T.admissionModePublic }}
            </option>
          </select>
          <div v-show="admissionMode === 'registration'" class="form-group">
            <input
              class="form-control mb-2 mt-2"
              type="text"
              readonly
              v-bind:value="courseURL"
            />
            <div class="form-inline">
              <button
                v-clipboard="courseURL"
                class="btn btn-primary"
                type="button"
                v-on:click="copiedToClipboard = true"
              >
                {{ T.wordsCopyToClipboard }}
              </button>
              <span v-if="copiedToClipboard === true" class="ml-3">
                <font-awesome-icon
                  icon="check-circle"
                  size="2x"
                  v-bind:style="{ color: 'green' }"
                />
                {{ T.passwordResetLinkCopiedToClipboard }}
              </span>
            </div>
          </div>
          <p class="help-block">
            <span v-html="admissionModeDescription"></span>
          </p>
        </div>
        <div class="text-right">
          <button class="btn btn-primary change-admission-mode" type="submit">
            {{ T.wordsSaveChanges }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import Clipboard from 'v-clipboard';
import T from '../../lang';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);
Vue.use(Clipboard);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class CourseAdmissionMode extends Vue {
  @Prop() initialAdmissionMode!: string;
  @Prop() admissionModeDescription!: string;
  @Prop() courseAlias!: string;
  @Prop() shouldShowPublicOption!: boolean;

  T = T;
  admissionMode = this.initialAdmissionMode;
  copiedToClipboard = false;

  onSubmit(): void {
    this.$emit('emit-update-admission-mode', this.admissionMode);
  }

  @Watch('copiedToClipboard')
  onPropertyChanged(): void {
    setTimeout(() => (this.copiedToClipboard = false), 5000);
  }

  @Watch('initialAdmissionMode')
  onCourseChange(): void {
    this.admissionMode = this.initialAdmissionMode;
  }

  get courseURL(): string {
    return `${window.location.origin}/course/${this.courseAlias}/`;
  }
}
</script>
