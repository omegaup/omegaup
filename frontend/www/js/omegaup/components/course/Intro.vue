<template>
  <div>
    <div class="card-header pb-4 px-5 pt-5">
      <h2 class="text-center mb-4">{{ course.name }}</h2>
      <omegaup-markdown
        :full-width="true"
        :markdown="course.description"
      ></omegaup-markdown>
      <!-- TODO: Here goes the estimated time for course -->
      <p v-if="course.level" class="text-center course-level">
        {{
          ui.formatString(T.courseIntroLevel, { level: levels[course.level] })
        }}
      </p>
      <template v-if="displayCoursePrivacyBullets">
        <omegaup-markdown
          :markdown="T.coursePrivacyConsent"
          :full-width="true"
          class="font-weight-bold h5"
        ></omegaup-markdown>
        <omegaup-markdown
          v-if="needsBasicInformation"
          :markdown="T.courseBasicInformationNeeded"
          :full-width="true"
        ></omegaup-markdown>
        <template v-if="course.requests_user_information != 'no'">
          <omegaup-markdown
            :markdown="statements.privacy.markdown || ''"
            :full-width="true"
          ></omegaup-markdown>
          <omegaup-radio-switch
            :value.sync="shareUserInformation"
            :selected-value="shareUserInformation"
            class="align-to-markdown ml-5 mb-3"
          ></omegaup-radio-switch>
        </template>

        <template v-if="shouldShowAcceptTeacher">
          <omegaup-markdown
            :markdown="statements.acceptTeacher.markdown || ''"
            :full-width="true"
          ></omegaup-markdown>
          <omegaup-radio-switch
            :value.sync="acceptTeacher"
            :selected-value="acceptTeacher"
            name="accept-teacher"
            class="align-to-markdown ml-5"
          ></omegaup-radio-switch>
        </template>
      </template>
      <div class="text-center mt-3">
        <template v-if="loggedIn">
          <form
            v-if="
              userRegistrationRequested === null || userRegistrationAccepted
            "
            @submit.prevent="onSubmit"
          >
            <button
              class="btn btn-primary btn-lg"
              name="start-course-submit"
              type="submit"
              :disabled="isButtonDisabled"
            >
              {{ T.startCourse }}
            </button>
          </form>
          <form
            v-else-if="!userRegistrationRequested"
            class="text-center"
            @submit.prevent="onRequestAccess"
          >
            <omegaup-markdown
              :markdown="T.mustRegisterToJoinCourse"
              :full-width="true"
            ></omegaup-markdown>
            <button type="submit" class="btn btn-primary btn-lg">
              {{ T.registerForCourse }}
            </button>
          </form>
          <omegaup-markdown
            v-else-if="!userRegistrationAnswered"
            :markdown="T.registrationPendingCourse"
            :full-width="true"
          ></omegaup-markdown>
          <omegaup-markdown
            v-else
            :markdown="T.registrationDenied"
            :full-width="true"
          ></omegaup-markdown>
        </template>
        <a
          v-else
          class="btn btn-primary"
          role="button"
          :href="`/login/?redirect=${encodeURIComponent(
            window.location.pathname,
          )}`"
          >{{ T.loginLogIn }}</a
        >
      </div>
    </div>
    <div class="mt-4">
      <div v-if="course.objective" class="mb-4">
        <h5 class="intro-subtitle pb-1">{{ T.courseIntroWhatYouWillLearn }}</h5>
        <omegaup-markdown
          :markdown="course.objective"
          :full-width="true"
        ></omegaup-markdown>
      </div>
      <div v-if="course.school_id && course.school_name">
        <h5 class="intro-subtitle pb-1 mb-2">{{ T.courseIntroImpartedBy }}</h5>
        {{ course.school_name }}
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import { types } from '../../api_types';

import omegaup_Markdown from '../Markdown.vue';
import omegaup_RadioSwitch from '../RadioSwitch.vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faChalkboardTeacher,
  faFileAlt,
  faListAlt,
} from '@fortawesome/free-solid-svg-icons';
library.add(faChalkboardTeacher, faFileAlt, faListAlt);

const levels = {
  introductory: T.courseLevelIntroductory,
  intermediate: T.courseLevelIntermediate,
  advanced: T.courseLevelAdvanced,
};

interface Statement {
  [name: string]: {
    gitObjectId?: string;
    markdown?: string;
    statementType?: string;
  };
}

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-radio-switch': omegaup_RadioSwitch,
  },
})
export default class CourseIntro extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() needsBasicInformation!: boolean;
  @Prop() shouldShowAcceptTeacher!: boolean;
  @Prop() statements!: Statement;
  @Prop({ default: null }) userRegistrationRequested!: boolean;
  @Prop({ default: null }) userRegistrationAnswered!: boolean;
  @Prop({ default: null }) userRegistrationAccepted!: boolean;
  @Prop() loggedIn!: boolean;

  T = T;
  ui = ui;
  levels = levels;

  shareUserInformation = false;
  acceptTeacher = false;
  window = window;

  get isButtonDisabled(): boolean {
    return (
      this.needsBasicInformation ||
      (this.course.requests_user_information === 'required' &&
        !this.shareUserInformation)
    );
  }

  get displayCoursePrivacyBullets(): boolean {
    return (
      (this.userRegistrationAccepted ||
        this.userRegistrationRequested === false) &&
      (this.needsBasicInformation ||
        this.course.requests_user_information != 'no' ||
        this.shouldShowAcceptTeacher)
    );
  }

  onSubmit(): void {
    this.$emit('submit', {
      shareUserInformation: this.shareUserInformation,
      acceptTeacher: this.acceptTeacher,
    });
  }

  onRequestAccess(): void {
    this.$emit('request-access-course', {
      shareUserInformation: this.shareUserInformation,
      acceptTeacher: this.acceptTeacher,
    });
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.course-level {
  color: $omegaup-pink;
}

h5.intro-subtitle {
  color: $omegaup-grey;
  width: 20rem;
  border-bottom: 4px solid $omegaup-primary--accent;
}
</style>
