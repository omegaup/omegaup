<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ T.courseList }}</h3>
    </div>

    <div class="m-3" v-if="isMainUserIdentity">
      <div class="float-right">
        <a class="btn btn-primary" href="/course/new/">{{ T.courseNew }}</a>
      </div>
      <h1>&nbsp;</h1>
    </div>
    <template
      v-for="(typeCourses, accessMode) in courses"
      v-if="typeCourses.activeTab !== ''"
    >
      <div class="ml-3">
        <h1>
          <span>{{ getDescription(accessMode) }}</span>
        </h1>
      </div>

      <omegaup-course-filtered-list
        v-bind:courses="typeCourses"
        v-bind:activeTab="typeCourses.activeTab"
      ></omegaup-course-filtered-list>
    </template>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as ui from '../../ui';
import course_FilteredList from './FilteredList.vue';

@Component({
  components: {
    'omegaup-course-filtered-list': course_FilteredList,
  },
})
export default class CourseList extends Vue {
  @Prop() courses!: omegaup.Course[];
  @Prop() isMainUserIdentity!: boolean;

  T = T;
  ui = ui;

  getDescription(admissionMode: string): string {
    if (admissionMode === 'public') return T.courseListPublicCourses;
    if (admissionMode === 'student') return T.courseListIStudy;
    if (admissionMode === 'admin') return T.courseListAdminCourses;
    return '';
  }
}
</script>
