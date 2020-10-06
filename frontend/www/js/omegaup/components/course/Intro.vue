<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.courseDetails }}</h2>
    </div>
    <div class="card-body text-center">
      <h2 name="name">{{ name }}</h2>
      <omegaup-markdown :markdown="description"></omegaup-markdown>
      <template
        v-if="userRegistrationRequested === null || userRegistrationAccepted"
      >
        <omegaup-markdown
          v-if="needsBasicInformation"
          :markdown="T.courseBasicInformationNeeded"
        ></omegaup-markdown>
        <template v-if="requestsUserInformation != 'no'">
          <omegaup-markdown
            :markdown="statements.privacy.markdown || ''"
          ></omegaup-markdown>
          <omegaup-radio-switch
            :value.sync="shareUserInformation"
            :selected-value="shareUserInformation"
          ></omegaup-radio-switch>
        </template>
        <template v-if="shouldShowAcceptTeacher">
          <omegaup-markdown
            :markdown="statements.acceptTeacher.markdown || ''"
          ></omegaup-markdown>
          <omegaup-radio-switch
            :value.sync="acceptTeacher"
            :selected-value="acceptTeacher"
            name="accept-teacher"
          ></omegaup-radio-switch>
        </template>
        <div class="text-center mt-3">
          <form @submit.prevent="onSubmit">
            <button
              class="btn btn-primary btn-lg"
              name="start-course-submit"
              type="submit"
              :disabled="isButtonDisabled"
            >
              {{ T.startCourse }}
            </button>
          </form>
        </div>
      </template>
      <template v-else>
        <form
          v-if="!userRegistrationRequested"
          @submit.prevent="$emit('request-access-course')"
        >
          <omegaup-markdown
            :markdown="T.mustRegisterToJoinCourse"
          ></omegaup-markdown>
          <button type="submit" class="btn btn-primary btn-lg">
            {{ T.registerForCourse }}
          </button>
        </form>
        <omegaup-markdown
          v-else-if="!userRegistrationAnswered"
          :markdown="T.registrationPendingCourse"
        ></omegaup-markdown>
        <omegaup-markdown
          v-else
          :markdown="T.registrationDenied"
        ></omegaup-markdown>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

import omegaup_Markdown from '../Markdown.vue';
import omegaup_RadioSwitch from '../RadioSwitch.vue';

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
    'omegaup-radio-switch': omegaup_RadioSwitch,
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
