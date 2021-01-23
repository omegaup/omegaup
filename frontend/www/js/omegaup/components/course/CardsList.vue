<template>
  <div>
    <div class="card-header mb-3">
      <h3>{{ T.courseCardAboutCourses }}</h3>
      <omegaup-markdown
        :markdown="T.courseCardDescriptionCourses"
      ></omegaup-markdown>
      <div class="text-right align-middle">
        <a href="/course/home/">{{ T.wordsReadMore }}</a>
      </div>
    </div>
    <div class="container">
      <template v-for="(typeCourses, accessMode) in courses">
        <div v-if="typeCourses.activeTab !== ''" :key="accessMode" class="row">
          <div class="col-lg-5 p-3 d-flex" :class="accessMode">
            <h4 class="flex-grow-1 mb-0">{{ getDescription(accessMode) }}</h4>
            <div
              class="d-inline-block"
              tabindex="0"
              data-toggle="tooltip"
              :title="T[`${accessMode}CourseInformationDescription`]"
            >
              <font-awesome-icon icon="info-circle" />
            </div>
          </div>
          <div class="col-lg-7 text-right align-self-center my-2 my-lg-0">
            <a :href="`/course/list/${accessMode}/`">{{
              T.courseListSeeAllCourses
            }}</a>
          </div>
          <div class="card col-lg-12 pt-3 mb-3">
            <template
              v-for="(filteredCourses, timeType) in typeCourses.filteredCourses"
            >
              <template v-if="timeType !== 'past'">
                <omegaup-course-card
                  v-for="course in filteredCourses.courses"
                  :key="course.alias"
                  :is-public="accessMode === 'public'"
                  :course-name="course.name"
                  :course-alias="course.alias"
                  :school-name="course.school_name"
                  :finish-time="course.finish_time"
                  :progress="course.progress"
                  :content="
                    course.admission_mode !== 'public' ? [] : course.assignments
                  "
                  :logged-in="loggedIn"
                  :is-open="course.is_open"
                  :show-topics="
                    course.admission_mode === 'public' &&
                    accessMode !== 'student'
                  "
                ></omegaup-course-card>
              </template>
            </template>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import course_CourseCard from './CourseCard.vue';
import omegaup_Markdown from '../Markdown.vue';

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
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
    'omegaup-course-card': course_CourseCard,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class CourseCardsList extends Vue {
  @Prop() courses!: types.StudentCourses;
  @Prop() loggedIn!: boolean;

  T = T;

  getDescription(admissionMode: string): string {
    if (admissionMode === 'public') return T.courseListPublicCourses;
    if (admissionMode === 'student') return T.courseListIStudy;
    return '';
  }
}
</script>

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
