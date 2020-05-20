<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="publish-form" v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{ T.contestNewFormAdmissionMode }}</label>
          <select
            class="form-control"
            name="admission-mode"
            v-model="admissionMode"
          >
            <option value="private">
              {{ T.admissionModeManualOnly }}
            </option>
            <option value="registration">
              {{ T.admissionModeShareURL }}
            </option>
            <option value="public" v-if="shouldShowPublicOption">
              {{ T.admissionModePublic }}
            </option>
          </select>
          <div
            class="form-group form-inline"
            v-show="admissionMode === 'registration'"
          >
            <input
              class="form-control"
              type="text"
              readonly
              v-bind:value="courseURL"
            />
            <a
              class="btn btn-primary btn-sm"
              role="button"
              v-clipboard="courseURL"
              >{{ T.wordsCopyToClipboard }}</a
            >
          </div>
          <p class="help-block">
            <span v-html="admissionModeDescription"></span>
          </p>
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
import { omegaup } from '../../omegaup';
import T from '../../lang';

@Component
export default class CourseAdmissionMode extends Vue {
  @Prop() initialAdmissionMode!: string;
  @Prop() admissionModeDescription!: string;
  @Prop() courseAlias!: string;
  @Prop() shouldShowPublicOption!: boolean;

  T = T;
  admissionMode = this.initialAdmissionMode;

  onSubmit(): void {
    this.$emit('emit-update-admission-mode', this.admissionMode);
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
