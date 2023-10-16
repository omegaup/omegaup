<template>
  <div class="card">
    <div class="card-body">
      <form class="form" @submit.prevent="onSubmit">
        <div class="form-group">
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

export enum AdmissionMode {
  Private = 'private',
  Registration = 'registration',
  Public = 'public',
}

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-toggle-switch': omegaup_ToggleSwitch,
  },
})
export default class Publish extends Vue {
  @Prop() admissionMode!: AdmissionMode;
  @Prop() admissionModeDescription!: string;
  @Prop() shouldShowPublicOption!: boolean;
  @Prop({ default: false }) defaultShowAllContestantsInScoreboard!: boolean;

  T = T;
  AdmissionMode = AdmissionMode;
  currentAdmissionMode = this.admissionMode;
  currentDefaultShowAllContestantsInScoreboard = this
    .defaultShowAllContestantsInScoreboard;

  onSubmit(): void {
    this.$emit('update-admission-mode', {
      admissionMode: this.currentAdmissionMode,
      defaultShowAllContestantsInScoreboard: this
        .currentDefaultShowAllContestantsInScoreboard,
    });
  }

  @Watch('admissionMode')
  onCourseChange(newValue: AdmissionMode): void {
    this.currentAdmissionMode = newValue;
  }
}
</script>
