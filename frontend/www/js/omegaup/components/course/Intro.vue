<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.courseDetails }}</h2>
    </div>
    <div class="panel-body">
      <h2 name="name">{{ name }}</h2>
      <p name="description">{{ description }}</p>
      <p v-html="T.courseBasicInformationNeeded"
         v-if="needsBasicInformation"></p>
      <template v-if="requestsUserInformation != 'no'">
        <p v-html="consentHtml"></p><label><input type="radio"
               v-bind:value="1"
               v-model="shareUserInformation"> {{ T.wordsYes }}</label> <label><input type="radio"
               v-bind:value="0"
               v-model="shareUserInformation"> {{ T.wordsNo }}</label>
      </template>
      <template v-if="shouldShowAcceptTeacher">
        <p v-html="acceptTeacherConsentHtml"></p><label><input name="accept-teacher"
               type="radio"
               v-model="acceptTeacher"
               value="yes"> {{ T.wordsYes }}</label> <label><input name="reject-teacher"
               type="radio"
               v-model="acceptTeacher"
               value="no"> {{ T.wordsNo }}</label>
      </template>
      <div class="text-center">
        <form v-on:submit.prevent="">
          <button class="btn btn-primary btn-lg"
                name="start-course-submit"
                type="button"
                v-bind:disabled="isButtonDisabled"
                v-on:click="onSubmit">{{ T.startCourse }}</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

interface Statement {
  [name: string]: {
    gitObjectId?: string;
    markdown?: string;
    statementType?: string;
  };
}

@Component
export default class CourseIntro extends Vue {
  @Prop() name!: string;
  @Prop() description!: string;
  @Prop() needsBasicInformation!: boolean;
  @Prop() requestsUserInformation!: string;
  @Prop() shouldShowAcceptTeacher!: boolean;
  @Prop() statements!: Statement;

  T = T;
  shareUserInformation = 0;
  markdownConverter = UI.markdownConverter();
  acceptTeacher = 'no';

  get consentHtml(): string {
    const markdown = this.statements.privacy.markdown || '';
    return this.markdownConverter.makeHtml(markdown);
  }

  get acceptTeacherConsentHtml(): string {
    const markdown = this.statements.acceptTeacher.markdown || '';
    return this.markdownConverter.makeHtml(markdown);
  }

  get isButtonDisabled(): boolean {
    return (
      this.needsBasicInformation ||
      (this.requestsUserInformation === 'required' &&
        this.shareUserInformation !== 1)
    );
  }

  onSubmit(): void {
    this.$emit('submit', this);
  }
}

</script>
