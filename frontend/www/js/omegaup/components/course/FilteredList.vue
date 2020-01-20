<template>
  <div class="panel-body tab-container">
    <ul class="nav nav-tabs">
      <li
        class="nav-item"
        v-bind:class="{
          active:
            courses.activeTab ===
            `${courses.type}-courses-${filteredCourses.type}`,
        }"
        v-if="filteredCourses.courses.length > 0"
        v-on:click="selectTabToShow(courses.type, filteredCourses.type)"
        v-for="filteredCourses in courses.filteredCourses"
      >
        <a data-toggle="tab">{{ tabName(filteredCourses.type) }}</a>
      </li>
    </ul>

    <div class="tab-content">
      <div
        class="tab-pane active"
        v-if="shouldShowTab(courses.type, filteredCourses.type)"
        v-for="filteredCourses in courses.filteredCourses"
      >
        <div class="panel">
          <div class="panel-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>{{ T.wordsName }}</th>
                  <th>{{ T.wordsStartTime }}</th>
                  <th>{{ T.wordsEndTime }}</th>
                  <th>{{ T.wordsNumHomeworks }}</th>
                  <th>{{ T.wordsNumTests }}</th>
                  <th colspan="2" v-if="courses.type === 'admin'">
                    {{ T.wordsActions }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="course in filteredCourses.courses">
                  <td>
                    <a v-bind:href="`/course/${course.alias}/`">{{
                      course.name
                    }}</a>
                  </td>
                  <td>{{ UI.formatDate(course.start_time) }}</td>
                  <td>
                    {{
                      course.finish_time
                        ? UI.formatDate(course.finish_time)
                        : T.wordsUnlimitedDuration
                    }}
                  </td>
                  <td>{{ course.counts.homework }}</td>
                  <td>{{ course.counts.test }}</td>
                  <template v-if="courses.type === 'admin'">
                    <td>
                      <a
                        class="glyphicon glyphicon-list-alt"
                        v-bind:href="`/course/${course.alias}/list/`"
                        v-bind:title="T.courseListSubmissionsByGroup"
                      ></a>
                    </td>
                    <td>
                      <a
                        class="glyphicon glyphicon-time"
                        v-bind:href="`/course/${course.alias}/activity/`"
                        v-bind:title="T.wordsActivityReport"
                      ></a>
                    </td>
                  </template>
                </tr>
              </tbody>
            </table>
          </div>
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

@Component
export default class CourseFilteredList extends Vue {
  @Prop() courses!: omegaup.FilteredCourses;

  T = T;
  UI = UI;
  showTabStudent = '';
  showTabAdmin = '';

  mounted() {
    this.$nextTick(function() {
      if (this.courses.type === 'student') {
        this.showTabStudent = this.courses.activeTab;
      } else if (this.courses.type === 'admin') {
        this.showTabAdmin = this.courses.activeTab;
      }
    });
  }

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
}
</script>
