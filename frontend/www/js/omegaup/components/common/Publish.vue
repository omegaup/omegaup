<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="publish-form" @submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{ T.contestNewFormAdmissionMode }}</label>
          <select
            v-model="admissionMode"
            class="form-control"
            name="admission-mode"
          >
            <option value="private">
              {{ T.wordsPrivate }}
            </option>
            <option value="registration">
              {{ T.wordsRegistration }}
            </option>
            <option v-if="shouldShowPublicOption" value="public">
              {{ T.wordsPublic }}
            </option>
          </select>
          <omegaup-markdown
            :markdown="admissionModeDescription"
          ></omegaup-markdown>
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
import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class Publish extends Vue {
  @Prop() initialAdmissionMode!: omegaup.AdmissionMode;
  @Prop() admissionModeDescription!: string;
  @Prop() shouldShowPublicOption!: boolean;

  T = T;
  admissionMode = this.initialAdmissionMode;

  onSubmit(): void {
    this.$emit('emit-update-admission-mode', this);
  }

  @Watch('initialAdmissionMode')
  onCourseChange(): void {
    this.admissionMode = this.initialAdmissionMode;
  }
}
</script>
