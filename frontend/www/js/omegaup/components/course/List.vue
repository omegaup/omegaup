<template>
  <div class="panel panel-primary">
    <div class="panel-heading">
      <h3 class="panel-title">{{ T.courseList }}</h3>
    </div>

    <template v-for="typeCourses in courses">
      <div class="page-header">
        <div class="pull-right">
          <a class="btn btn-primary" href="/course/new/">{{ T.courseNew }}</a>
        </div>
        <h1>
          <span>{{ typeCourses.name }}</span>
        </h1>
      </div>

      <div class="panel-body tab-container">
        <ul class="nav nav-tabs">
          <li
            class="nav-item"
            v-bind:class="{
              active:
                typeCourses.activeTab ===
                `${typeCourses.type}-courses-${filteredCourses.type}`,
            }"
            v-if="filteredCourses.courses.length > 0"
            v-on:click="selectTabToShow(typeCourses.type, filteredCourses.type)"
            v-for="filteredCourses in typeCourses.filteredCourses"
          >
            <a data-toggle="tab">{{ tabName(filteredCourses.type) }}</a>
          </li>
        </ul>

        <div class="tab-content">
          <div
            class="tab-pane active"
            v-if="shouldShowTab(typeCourses.type, filteredCourses.type)"
            v-for="filteredCourses in typeCourses.filteredCourses"
          >
            <omegaup-course-filtered-list
              v-bind:courses="filteredCourses.courses"
            ></omegaup-course-filtered-list>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import course_FilteredList from './FilteredList.vue';

interface typeCourse {
  type: string;
  filteredCourses: filteredCourses[];
  name: string;
  activeTab: string;
}

interface filteredCourses {
  type: string;
  courses: omegaup.Course[];
}

@Component({
  components: {
    'omegaup-course-filtered-list': course_FilteredList,
  },
})
export default class CourseList extends Vue {
  @Prop() courses!: typeCourse[];
  @Prop() initialActiveTabStudent!: string;
  @Prop() initialActiveTabAdmin!: string;

  T = T;
  UI = UI;
  showTabStudent = '';
  showTabAdmin = '';

  tabName(filteredTypeCourses: string): string {
    if (filteredTypeCourses === 'current') {
      return this.T.courseListStudentCurrentCourses;
    } else if (filteredTypeCourses === 'past') {
      return this.T.courseListStudentPastCourses;
    }
    return '';
  }

  selectTabToShow(typeCourses: string, filteredTypeCourses: string): void {
    if (typeCourses === 'student') {
      this.showTabStudent = `student-courses-${filteredTypeCourses}`;
    } else if (typeCourses === 'admin') {
      this.showTabAdmin = `admin-courses-${filteredTypeCourses}`;
    }
  }

  shouldShowTab(typeCourses: string, filteredTypeCourses: string): boolean {
    if (typeCourses === 'student') {
      return this.showTabStudent === `student-courses-${filteredTypeCourses}`;
    }
    return this.showTabAdmin === `admin-courses-${filteredTypeCourses}`;
  }

  @Watch('initialActiveTabStudent')
  onPropertyChanged(activeTab: string, oldActiveTab: string): void {
    this.showTabStudent = activeTab;
  }

  @Watch('initialActiveTabAdmin')
  onPropertyHasChanged(activeTab: string, oldActiveTab: string): void {
    this.showTabAdmin = activeTab;
  }
}
</script>
