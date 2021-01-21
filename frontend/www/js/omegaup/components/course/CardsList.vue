<template>
  <div>
    <div class="card-header mb-3">
      <h1>{{ T.courseCardAboutCourses }}</h1>
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
            <h3 class="flex-grow-1 text-white">
              {{ getDescription(accessMode) }}
            </h3>
            <a
              role="button"
              class="text-white"
              data-toggle="modal"
              :data-target="`.${accessMode}-modal`"
            >
              <font-awesome-icon
                icon="info-circle"
                :title="T[`${accessMode}CourseInformationDescription`]"
              />
            </a>
            <div
              class="modal text-black"
              :class="`${accessMode}-modal`"
              tabindex="-1"
            >
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">
                      {{ getDescription(accessMode) }}
                    </h5>
                    <button
                      type="button"
                      class="close"
                      data-dismiss="modal"
                      aria-label="Close"
                    >
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <omegaup-markdown
                      :markdown="T[`${accessMode}CoursesModal`]"
                    ></omegaup-markdown>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-7 text-right align-middle">
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
export default class CourseList extends Vue {
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

.public {
  background: $omegaup-pink;
}

.student {
  background: $omegaup-blue;
}
</style>
