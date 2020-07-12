<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.courseDetails }}</h2>
    </div>
    <div class="panel-body text-center">
      <h2 name="name">{{ name }}</h2>
      <omegaup-markdown v-bind:markdown="description"></omegaup-markdown>
      <template
        v-if="userRegistrationRequested === null || userRegistrationAccepted"
      >
        <p
          v-html="T.courseBasicInformationNeeded"
          v-if="needsBasicInformation"
        ></p>
        <template v-if="requestsUserInformation != 'no'">
          <omegaup-markdown
            v-bind:markdown="statements.privacy.markdown || ''"
          ></omegaup-markdown>
          <label
            ><input
              type="radio"
              v-bind:value="true"
              v-model="shareUserInformation"
            />
            {{ T.wordsYes }}</label
          >
          <label
            ><input
              type="radio"
              v-bind:value="false"
              v-model="shareUserInformation"
            />
            {{ T.wordsNo }}</label
          >
        </template>
        <template v-if="shouldShowAcceptTeacher">
          <omegaup-markdown
            v-bind:markdown="statements.acceptTeacher.markdown || ''"
          ></omegaup-markdown>
          <label
            ><input
              name="accept-teacher"
              type="radio"
              v-bind:value="true"
              v-model="acceptTeacher"
            />
            {{ T.wordsYes }}</label
          >
          <label
            ><input
              name="reject-teacher"
              type="radio"
              v-bind:value="false"
              v-model="acceptTeacher"
            />
            {{ T.wordsNo }}</label
          >
        </template>
        <div class="text-center">
          <form v-on:submit.prevent="onSubmit">
            <button
              class="btn btn-primary btn-lg"
              name="start-course-submit"
              type="submit"
              v-bind:disabled="isButtonDisabled"
            >
              {{ T.startCourse }}
            </button>
          </form>
        </div>
      </template>
      <template v-else="">
        <form
          v-if="!userRegistrationRequested"
          v-on:submit.prevent="$emit('request-access-course')"
        >
          <p v-html="T.mustRegisterToJoinCourse"></p>
          <button type="submit" class="btn btn-primary btn-lg">
            {{ T.registerForCourse }}
          </button>
        </form>
        <p
          v-else-if="!userRegistrationAnswered"
          v-html="T.registrationPendingCourse"
        ></p>
        <p v-else="" v-html="T.registrationDenied"></p>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

import omegaup_Markdown from '../Markdown.vue';

interface Statement {
  [name: string]: {
    gitObjectId?: string;
    markdown?: string;
    statementType?: string;
  };
}

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class CourseIntro extends Vue {
  @Prop() name!: string;
  @Prop() description!: string;
  @Prop() needsBasicInformation!: boolean;
  @Prop() requestsUserInformation!: string;
  @Prop() shouldShowAcceptTeacher!: boolean;
  @Prop() statements!: Statement;
  @Prop({ default: null }) userRegistrationRequested!: boolean;
  @Prop({ default: null }) userRegistrationAnswered!: boolean;
  @Prop({ default: null }) userRegistrationAccepted!: boolean;

  T = T;
  shareUserInformation = false;
  acceptTeacher = false;

  get isButtonDisabled(): boolean {
    return (
      this.needsBasicInformation ||
      (this.requestsUserInformation === 'required' &&
        !this.shareUserInformation)
    );
  }

  onSubmit(): void {
    this.$emit('submit', this);
  }
}
</script>
