<template>
  <div>
    <div class="card-header p-5">
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
      <template
        v-if="userRegistrationRequested === null || userRegistrationAccepted"
      >
        <omegaup-markdown
          v-if="needsBasicInformation"
          :markdown="T.courseBasicInformationNeeded"
          :full-width="true"
        ></omegaup-markdown>
        <template v-if="requestsUserInformation != 'no'">
          <omegaup-markdown
            :markdown="statements.privacy.markdown || ''"
            :full-width="true"
          ></omegaup-markdown>
          <omegaup-radio-switch
            :value.sync="shareUserInformation"
            :selected-value="shareUserInformation"
            class="align-to-markdown"
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
            class="align-to-markdown"
          ></omegaup-radio-switch>
        </template>
        <div class="text-center mt-3">
          <form v-if="loggedIn" @submit.prevent="onSubmit">
            <button
              class="btn btn-primary btn-lg"
              name="start-course-submit"
              type="submit"
              :disabled="isButtonDisabled"
            >
              {{ T.startCourse }}
            </button>
          </form>
          <a
            v-else
            class="btn btn-primary"
            :href="`/login/?redirect=${encodeURIComponent(
              window.location.pathname,
            )}`"
            >{{ T.loginLogIn }}</a
          >
        </div>
      </template>
      <template v-else>
        <form
          v-if="!userRegistrationRequested"
          @submit.prevent="$emit('request-access-course')"
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
    </div>
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">{{ T.courseDetails }}</h2>
      </div>
      <div class="card-body text-center">
        <h2 name="name">{{ course.name }}</h2>
        <omegaup-markdown :markdown="course.description"></omegaup-markdown>
        <div v-if="course !== null" class="my-4 card align-to-markdown">
          <h5 class="card-header">{{ T.wordsContent }}</h5>
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead>
                <tr>
                  <th class="text-center" scope="col">
                    {{ T.wordsContentType }}
                  </th>
                  <th class="text-center" scope="col">{{ T.wordsName }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!course.assignments.length">
                  <td class="empty-table-message" colspan="2">
                    {{ T.courseContentEmpty }}
                  </td>
                </tr>
                <tr
                  v-for="assignment in course.assignments"
                  v-else
                  :key="assignment.alias"
                >
                  <td class="text-center">
                    <template v-if="assignment.assignment_type === 'homework'">
                      <font-awesome-icon icon="file-alt" />
                      <span class="ml-2">{{ T.wordsHomework }}</span>
                    </template>
                    <template
                      v-else-if="assignment.assignment_type === 'lesson'"
                    >
                      <font-awesome-icon icon="chalkboard-teacher" />
                      <span class="ml-2">{{ T.wordsLesson }}</span>
                    </template>
                    <template v-else>
                      <font-awesome-icon icon="list-alt" />
                      <span class="ml-2">{{ T.wordsExam }}</span>
                    </template>
                  </td>
                  <td>
                    <span>{{ assignment.name }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
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
              class="align-to-markdown"
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
              class="align-to-markdown"
            ></omegaup-radio-switch>
          </template>
          <div class="text-center mt-3">
            <form v-if="loggedIn" @submit.prevent="onSubmit">
              <button
                class="btn btn-primary btn-lg"
                name="start-course-submit"
                type="submit"
                :disabled="isButtonDisabled"
              >
                {{ T.startCourse }}
              </button>
            </form>
            <a
              v-else
              class="btn btn-primary"
              :href="`/login/?redirect=${encodeURIComponent(
                window.location.pathname,
              )}`"
              >{{ T.loginLogIn }}</a
            >
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

  onSubmit(): void {
    this.$emit('submit', this);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.course-level {
  color: $omegaup-pink;
}
</style>
