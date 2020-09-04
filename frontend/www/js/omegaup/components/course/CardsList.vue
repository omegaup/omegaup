<template>
  <div>
    <h1 class="card-title">{{ T.navAllCourses }}</h1>
    <div class="card-header mb-3">
      <h1>{{ T.courseCardAboutCourses }}</h1>
      <p v-html="T.courseCardDescriptionCourses"></p>
      <div class="text-right align-middle">
        <a href="https://blog.omegaup.com/cursos-en-omegaup/">{{
          T.wordsReadMore
        }}</a>
      </div>
    </div>
    <div class="container">
      <div
        class="row"
        v-for="(typeCourses, accessMode) in courses"
        v-if="typeCourses.activeTab !== ''"
      >
        <div class="col-lg-5 p-3 d-flex" v-bind:class="accessMode">
          <h3 class="flex-grow-1">{{ getDescription(accessMode) }}</h3>
          <div
            class="d-inline-block"
            tabindex="0"
            data-toggle="tooltip"
            v-bind:title="T[`${accessMode}CourseInformationDescription`]"
          >
            <font-awesome-icon icon="info-circle" />
          </div>
        </div>
        <div class="col-lg-7 text-right align-middle">
          <a v-bind:href="`/course/list/${accessMode}/`">{{
            T.courseListSeeAllCourses
          }}</a>
        </div>
        <div class="card col-lg-12 pt-3 mb-3">
          <template
            v-for="(filteredCourses, timeType) in typeCourses.filteredCourses"
          >
            <omegaup-course-card
              v-for="course in filteredCourses.courses"
              v-if="timeType !== 'past'"
              v-bind:key="course.alias"
              v-bind:course-name="course.name"
              v-bind:course-alias="course.alias"
              v-bind:school-name="course.school_name"
              v-bind:finish-time="course.finish_time"
              v-bind:progress="course.progress"
              v-bind:content="
                course.admission_mode !== 'public' ? [] : course.assignments
              "
              v-bind:is-open="course.is_open"
              v-bind:show-topics="
                course.admission_mode === 'public' && accessMode !== 'student'
              "
            ></omegaup-course-card>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.student,
.public {
  color: $omegaup-white;
}

.public {
  background: $omegaup-pink;
}

.student {
  background: $omegaup-blue;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import course_CourseCard from './CourseCard.vue';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    'omegaup-course-card': course_CourseCard,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class CourseList extends Vue {
  @Prop() courses!: types.StudentCourses;

  T = T;

  getDescription(admissionMode: string): string {
    if (admissionMode === 'public') return T.courseListPublicCourses;
    if (admissionMode === 'student') return T.courseListIStudy;
    return '';
  }
}
</script>
