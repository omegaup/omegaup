<template>
  <div class="panel panel-primary">
    <div class="panel-heading">
      <h3 class="panel-title">{{ T.courseList }}</h3>
    </div>

    <div class="page-header">
      <div class="pull-right">
        <a class="btn btn-primary" href="/course/new/">{{ T.courseNew }}</a>
      </div>
      <h1>
        <span>{{ T.courseList }}</span> <small></small>
      </h1>
    </div>

    <div class="panel-body tab-container">
      <ul class="nav nav-tabs">
        <li
          class="nav-item"
          v-bind:class="{
            active: showTabStudent === 'student-courses-current',
          }"
          v-if="studentCoursesCurrent.length > 0"
          v-on:click="showTabStudent = 'student-courses-current'"
        >
          <a data-toggle="tab">{{ T.courseListStudentCurrentCourses }}</a>
        </li>
        <li
          class="nav-item"
          v-bind:class="{ active: showTabStudent === 'student-courses-past' }"
          v-if="studentCoursesPast.length > 0"
          v-on:click="showTabStudent = 'student-courses-past'"
        >
          <a data-toggle="tab">{{ T.courseListStudentPastCourses }}</a>
        </li>
      </ul>

      <div class="tab-content">
        <div
          class="tab-pane active"
          v-if="showTabStudent === 'student-courses-current'"
        >
          <omegaup-course-filtered-list
            v-bind:courses="studentCoursesCurrent"
          ></omegaup-course-filtered-list>
        </div>
        <div
          class="tab-pane active"
          v-if="showTabStudent === 'student-courses-past'"
        >
          <omegaup-course-filtered-list
            v-bind:courses="studentCoursesPast"
          ></omegaup-course-filtered-list>
        </div>
      </div>
    </div>

    <div class="page-header">
      <h1>
        <span>{{ T.courseListAdminCourses }}</span> <small></small>
      </h1>
    </div>

    <div class="panel-body tab-container">
      <ul class="nav nav-tabs">
        <li
          class="nav-item"
          v-bind:class="{ active: showTabAdmin === 'admin-courses-current' }"
          v-if="adminCoursesCurrent.length > 0"
          v-on:click="showTabAdmin = 'admin-courses-current'"
        >
          <a data-toggle="tab">{{ T.courseListStudentCurrentCourses }}</a>
        </li>
        <li
          class="nav-item"
          v-bind:class="{ active: showTabAdmin === 'admin-courses-past' }"
          v-if="adminCoursesPast.length > 0"
          v-on:click="showTabAdmin = 'admin-courses-past'"
        >
          <a data-toggle="tab">{{ T.courseListStudentPastCourses }}</a>
        </li>
      </ul>

      <div class="tab-content">
        <div
          class="tab-pane active"
          v-if="showTabAdmin === 'admin-courses-current'"
        >
          <omegaup-course-filtered-list
            v-bind:courses="adminCoursesCurrent"
          ></omegaup-course-filtered-list>
        </div>
        <div
          class="tab-pane active"
          v-if="showTabAdmin === 'admin-courses-past'"
        >
          <omegaup-course-filtered-list
            v-bind:courses="adminCoursesPast"
          ></omegaup-course-filtered-list>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import course_FilteredList from './FilteredList.vue';

@Component({
  components: {
    'omegaup-course-filtered-list': course_FilteredList,
  },
})
export default class CourseList extends Vue {
  @Prop() studentCoursesCurrent!: omegaup.Course[];
  @Prop() studentCoursesPast!: omegaup.Course[];
  @Prop() adminCoursesCurrent!: omegaup.Course[];
  @Prop() adminCoursesPast!: omegaup.Course[];
  @Prop() initialActiveTabStudent!: string;
  @Prop() initialActiveTabAdmin!: string;

  T = T;
  UI = UI;
  showTabStudent = '';
  showTabAdmin = '';

  @Watch('initialActiveTabStudent')
  onPropertyChanged(activeTab: string, oldActiveTab: string) {
    this.showTabStudent = activeTab;
  }

  @Watch('initialActiveTabAdmin')
  onPropertyHasChanged(activeTab: string, oldActiveTab: string) {
    this.showTabAdmin = activeTab;
  }
}
</script>
