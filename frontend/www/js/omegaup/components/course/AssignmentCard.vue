<template>
  <div class="card ml-3 mr-3 mb-3">
    <div class="mx-3 mt-3 d-flex justify-content-between align-items-center">
      <div class="font-weight-bold">
        <h5 class="mb-1">
          <a :href="`/course/${courseAlias}/assignment/${assignment.alias}`">{{
            assignment.name
          }}</a>
        </h5>
        <p class="mb-0 assignment-type">
          <template v-if="assignment.assignment_type === 'homework'">
            <font-awesome-icon icon="file-alt" />
            <span class="ml-2">{{ T.wordsHomework }}</span>
          </template>
          <template v-else-if="assignment.assignment_type === 'lesson'">
            <font-awesome-icon icon="chalkboard-teacher" />
            <span class="ml-2">{{ T.wordsLesson }}</span>
          </template>
          <template v-else>
            <font-awesome-icon icon="list-alt" />
            <span class="ml-2">{{ T.wordsExam }}</span>
          </template>
        </p>
      </div>
      <div>
        <a
          :href="`/course/${courseAlias}/assignment/${assignment.alias}`"
          class="btn btn-primary d-inline-block text-white"
          data-course-start-assignment-button
          >{{
            assignment.opened ? T.courseCardCourseResume : T.assignmentCardStart
          }}</a
        >
      </div>
    </div>
    <div class="dropdown-divider"></div>
    <div class="row mx-3 mb-2 justify-content-between align-items-center">
      <div class="col-8 p-0">
        {{
          assignment.assignment_type === 'lesson'
            ? ui.formatString(T.assignmentCardLessons, {
                lessonCount: assignment.problemCount,
              })
            : ui.formatString(T.assignmentCardProblems, {
                problemCount: assignment.problemCount,
              })
        }}
      </div>
      <div
        v-if="assignment.assignment_type !== 'lesson'"
        class="col-4 p-0 d-flex align-items-center"
      >
        {{ studentProgress.toFixed(0) }}%
        <div class="progress ml-1 w-100">
          <div
            class="progress-bar"
            role="progressbar"
            :aria-valuenow="studentProgress"
            aria-valuemin="0"
            aria-valuemax="100"
            :style="`width: ${studentProgress}%`"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';
import omegaup_Markdown from '../Markdown.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faChalkboardTeacher,
  faFileAlt,
  faListAlt,
} from '@fortawesome/free-solid-svg-icons';
library.add(faChalkboardTeacher, faFileAlt, faListAlt);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class AssignmentCard extends Vue {
  @Prop() courseAlias!: string;
  @Prop() assignment!: types.CourseAssignment;
  @Prop() studentProgress!: number;

  T = T;
  ui = ui;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.assignment-type {
  font-size: 1.05rem;
  font-weight: 600;
}
.progress-bar {
  background-color: $omegaup-yellow;
}
</style>
