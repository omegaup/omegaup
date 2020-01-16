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

    <div class="panel-body tab-container" style="display:none">
      <ul class="nav nav-tabs">
        <li class="nav-item" v-show="studentCoursesCurrent.length > 0">
          <a
            class="nav-link"
            href="#tab-student-courses-current"
            data-toggle="tab"
            >{{ T.courseListStudentCurrentCourses }}</a
          >
        </li>
        <li class="nav-item" v-show="studentCoursesPast.length > 0">
          <a
            class="nav-link"
            href="#tab-student-courses-past"
            data-toggle="tab"
            >{{ T.courseListStudentPastCourses }}</a
          >
        </li>
      </ul>

      <div class="tab-content">
        <!-- id-lint off -->
        <div class="tab-pane" id="tab-student-courses-current">
          <!-- id-lint on -->
          <omegaup-course-filtered-list
            v-bind:courses="studentCoursesCurrent"
          ></omegaup-course-filtered-list>
        </div>
        <!-- id-lint off -->
        <div class="tab-pane" id="tab-student-courses-past">
          <!-- id-lint on -->
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

    <div class="panel-body tab-container" style="display:none">
      <ul class="nav nav-tabs">
        <li class="nav-item" v-show="adminCoursesCurrent.length > 0">
          <a
            class="nav-link"
            href="#tab-admin-courses-current"
            data-toggle="tab"
            >{{ T.courseListStudentCurrentCourses }}</a
          >
        </li>
        <li class="nav-item" v-show="adminCoursesPast.length > 0">
          <a
            class="nav-link"
            href="#tab-admin-courses-past"
            data-toggle="tab"
            >{{ T.courseListStudentPastCourses }}</a
          >
        </li>
      </ul>

      <div class="tab-content">
        <!-- id-lint off -->
        <div class="tab-pane" id="tab-admin-courses-current">
          <!-- id-lint on -->
          <omegaup-course-filtered-list
            v-bind:courses="adminCoursesCurrent"
          ></omegaup-course-filtered-list>
        </div>
        <!-- id-lint off -->
        <div class="tab-pane" id="tab-admin-courses-past">
          <!-- id-lint on -->
          <omegaup-course-filtered-list
            v-bind:courses="adminCoursesPast"
          ></omegaup-course-filtered-list>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import course_FiltedList from './FilteredList.vue';

@Component({
  components: {
    'omegaup-course-filtered-list': course_FiltedList,
  },
})
export default class CourseList extends Vue {
  @Prop() adminCoursesCurrent!: omegaup.Course[];
  @Prop() adminCoursesPast!: omegaup.Course[];
  @Prop() studentCoursesCurrent!: omegaup.Course[];
  @Prop() studentCoursesPast!: omegaup.Course[];

  T = T;
  UI = UI;
}
</script>
