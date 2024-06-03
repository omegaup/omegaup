<template>
  <div class="card">
    <div class="card-body">
      <form class="form" @submit.prevent="onSubmit">
        <div class="form-group">
          <div class="input-group m-2">
            <input
              class="form-control"
              type="text"
              readonly
              :value="contestURL"
            />
            <div class="input-group-append">
              <button
                v-clipboard="contestURL"
                copy-to-clipboard
                class="btn btn-primary"
                type="button"
                :title="T.contestEditCopyContestLink"
                @click="onCopyContestLink"
              >
                <font-awesome-icon icon="clipboard" />
              </button>
            </div>
          </div>

          <label>{{ T.contestNewFormAdmissionMode }}</label>
          <select
            v-model="currentAdmissionMode"
            class="form-control"
            name="admission-mode"
          >
            <option :value="AdmissionMode.Private">
              {{ T.wordsPrivate }}
            </option>
            <option :value="AdmissionMode.Registration">
              {{ T.wordsRegistration }}
            </option>
            <option v-if="shouldShowPublicOption" :value="AdmissionMode.Public">
              {{ T.wordsPublic }}
            </option>
          </select>
          <p class="form-text text-muted">
            <omegaup-markdown
              :markdown="admissionModeDescription"
            ></omegaup-markdown>
          </p>
        </div>
        <div class="form-group">
          <omegaup-toggle-switch
            v-if="currentAdmissionMode !== AdmissionMode.Private"
            :value.sync="currentDefaultShowAllContestantsInScoreboard"
            :checked-value="currentDefaultShowAllContestantsInScoreboard"
            :text-description="T.showDefaultAllContestantsInScoreboard"
          ></omegaup-toggle-switch>
        </div>
        <button class="btn btn-primary change-admission-mode" type="submit">
          {{ T.wordsSaveChanges }}
        </button>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import omegaup_Markdown from '../Markdown.vue';
import omegaup_ToggleSwitch from '../ToggleSwitch.vue';
import Clipboard from 'v-clipboard';
import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);
Vue.use(Clipboard);

export enum AdmissionMode {
  Private = 'private',
  Registration = 'registration',
  Public = 'public',
}

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-toggle-switch': omegaup_ToggleSwitch,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class ContestEditPublish extends Vue {
  @Prop() admissionMode!: AdmissionMode;
  @Prop() admissionModeDescription!: string;
  @Prop() alias!: string;
  @Prop() shouldShowPublicOption!: boolean;
  @Prop({ default: false }) defaultShowAllContestantsInScoreboard!: boolean;

  T = T;
  AdmissionMode = AdmissionMode;
  currentAdmissionMode = this.admissionMode;
  currentDefaultShowAllContestantsInScoreboard = this
    .defaultShowAllContestantsInScoreboard;

  get contestURL(): string {
    return `${window.location.origin}/arena/${this.alias}/startfresh/`;
  }

  onSubmit(): void {
    this.$emit('update-admission-mode', {
      admissionMode: this.currentAdmissionMode,
      defaultShowAllContestantsInScoreboard: this
        .currentDefaultShowAllContestantsInScoreboard,
    });
  }

  onCopyContestLink(): void {
    this.$emit('show-copy-message');
  }

  @Watch('admissionMode')
  onCourseChange(newValue: AdmissionMode): void {
    this.currentAdmissionMode = newValue;
  }
}
</script>
