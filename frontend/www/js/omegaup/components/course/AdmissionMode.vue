<template>
  <div class="card">
    <div class="card-body">
      <form
        class="publish-form"
        data-course-admission-mode-form
        @submit.prevent="onSubmit"
      >
        <div class="form-group">
          <label>{{ T.courseEditAdmissionModeSelect }}</label>
          <a
            data-toggle="tooltip"
            rel="tooltip"
            :title="T.courseEditAdmissionModeDescription"
          >
            <img src="/media/question.png" alt="Help" />
          </a>
          <div class="form-group">
            <select
              v-model="currentAdmissionMode"
              class="form-control"
              name="admission-mode"
            >
              <option :value="AdmissionMode.Private">
                {{ T.admissionModeManualOnly }}
              </option>
              <option :value="AdmissionMode.Registration">
                {{ T.admissionModeShareURL }}
              </option>
              <option :value="AdmissionMode.Public">
                {{ T.admissionModePublic }}
              </option>
            </select>
          </div>
          <div
            v-if="
              currentAdmissionMode === AdmissionMode.Registration ||
              currentAdmissionMode === AdmissionMode.Public
            "
            class="form-group"
          >
            <input
              class="form-control mb-2 mt-2"
              type="text"
              readonly
              :value="courseURL"
            />
            <div class="form-inline">
              <button
                v-clipboard="courseURL"
                class="btn btn-primary"
                type="button"
                @click="copiedToClipboard = true"
              >
                {{ T.wordsCopyToClipboard }}
              </button>
              <span v-if="copiedToClipboard === true" class="ml-3">
                <font-awesome-icon
                  icon="check-circle"
                  size="2x"
                  :style="{ color: 'green' }"
                />
                {{ T.passwordResetLinkCopiedToClipboard }}
              </span>
            </div>
          </div>
          <div
            v-if="currentAdmissionMode === AdmissionMode.Public"
            class="form-group"
            data-toggle-public-course-list
          >
            <omegaup-toggle-switch
              v-if="shouldShowPublicOption"
              :value.sync="currentShowInPublicCoursesList"
              :checked-value="currentShowInPublicCoursesList"
              :text-description="T.courseEditShowInPublicCoursesList"
            ></omegaup-toggle-switch>
            <omegaup-markdown
              v-else
              :markdown="T.courseEditRequestSetRecommendedCourse"
            ></omegaup-markdown>
          </div>
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
import omegaup_Markdown from '../Markdown.vue';
import omegaup_ToggleSwitch from '../ToggleSwitch.vue';
import { AdmissionMode } from '../common/Publish.vue';

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
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-toggle-switch': omegaup_ToggleSwitch,
  },
})
export default class CourseAdmissionMode extends Vue {
  @Prop() admissionMode!: AdmissionMode;
  @Prop() courseAlias!: string;
  @Prop() shouldShowPublicOption!: boolean;
  @Prop({ default: false }) showInPublicCoursesList!: boolean;

  T = T;
  AdmissionMode = AdmissionMode;
  currentAdmissionMode = this.admissionMode;
  currentShowInPublicCoursesList = this.showInPublicCoursesList;
  copiedToClipboard = false;

  onSubmit(): void {
    this.$emit('update-admission-mode', {
      admissionMode: this.currentAdmissionMode,
      showInPublicCoursesList: this.currentShowInPublicCoursesList,
    });
  }

  @Watch('copiedToClipboard')
  onPropertyChanged(): void {
    setTimeout(() => (this.copiedToClipboard = false), 5000);
  }

  @Watch('admissionMode')
  onCourseChange(): void {
    this.currentAdmissionMode = this.admissionMode;
  }

  get courseURL(): string {
    return `${window.location.origin}/course/${this.courseAlias}/`;
  }
}
</script>
