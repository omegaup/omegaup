<template>
  <div>
    <h1 class="card-title">{{ T.navAllCourses }}</h1>

    <div class="m-3" v-if="false">
      <div class="float-right">
        <a class="btn btn-primary" href="/course/new/">{{ T.courseNew }}</a>
      </div>
      <h1>&nbsp;</h1>
    </div>
    <div class="container">
      <div
        class="row"
        v-for="(typeCourses, accessMode) in courses"
        v-if="typeCourses.activeTab !== ''"
      >
        <div class="col-lg-5 p-3" v-bind:class="accessMode">
          <div class="float-left">
            <h3>{{ getDescription(accessMode) }}</h3>
          </div>
          <div class="float-right">
            <font-awesome-icon icon="info-circle" />
          </div>
        </div>
        <div class="col-lg-7 text-right align-middle">
          <span
            ><a v-bind:href="`/course/list/${accessMode}/`">{{
              T.courseListSeeAll
            }}</a></span
          >
        </div>
        <div class="card col-lg-12 pt-3 mb-3">
          <template
            v-for="(filteredCourses, timeType) in typeCourses.filteredCourses"
          >
            <template
              v-for="course in filteredCourses.courses"
              v-if="timeType !== 'past'"
            >
              <omegaup-course-card
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
