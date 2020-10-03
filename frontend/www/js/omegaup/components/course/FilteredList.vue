<template>
  <div class="card-body tab-container">
    <ul class="nav nav-tabs">
      <li
        class="nav-item"
        v-if="filteredCourses.courses"
        v-on:click="showTab = filteredCourses.timeType"
        v-for="(filteredCourses, timeType) in courses.filteredCourses"
      >
        <a
          data-toggle="tab"
          class="nav-link"
          href="#"
          v-bind:class="{ active: activeTab === filteredCourses.timeType }"
          >{{ getTabName(filteredCourses.timeType) }}</a
        >
      </li>
    </ul>

    <div class="tab-content">
      <div
        class="tab-pane active"
        v-if="showTab === filteredCourses.timeType"
        v-for="filteredCourses in courses.filteredCourses"
      >
        <div class="panel">
          <div class="panel-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>{{ T.wordsName }}</th>
                  <th v-if="showPercentage">
                    {{ T.wordsCompletedPercentage }}
                  </th>
                  <th>{{ T.wordsDueDate }}</th>
                  <th>{{ T.wordsNumHomeworks }}</th>
                  <th>{{ T.wordsNumTests }}</th>
                  <th colspan="3" v-if="courses.accessMode === 'admin'">
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
                  <td v-if="showPercentage">
                    {{ `${course.progress}%` }}
                  </td>
                  <td>
                    {{
                      course.finish_time
                        ? time.formatDate(course.finish_time)
                        : T.wordsUnlimitedDuration
                    }}
                  </td>
                  <td>
                    {{
                      course.counts.homework
                        ? course.counts.homework
                        : T.wordsNotApplicable
                    }}
                  </td>
                  <td>
                    {{
                      course.counts.test
                        ? course.counts.test
                        : T.wordsNotApplicable
                    }}
                  </td>
                  <template v-if="courses.accessMode === 'admin'">
                    <td>
                      <a
                        v-bind:href="`/course/${course.alias}/edit/`"
                        v-bind:title="T.omegaupTitleCourseEdit"
                      >
                        <font-awesome-icon icon="edit" />
                      </a>
                    </td>
                    <td>
                      <a
                        v-bind:href="`/course/${course.alias}/list/`"
                        v-bind:title="T.courseListSubmissionsByGroup"
                      >
                        <font-awesome-icon icon="list-alt" />
                      </a>
                    </td>
                    <td>
                      <a
                        v-bind:href="`/course/${course.alias}/activity/`"
                        v-bind:title="T.activityReport"
                      >
                        <font-awesome-icon icon="clock" />
                      </a>
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

<style>
.nav-tabs .nav-item {
  margin-bottom: -2px;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';

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
  },
})
export default class CourseFilteredList extends Vue {
  @Prop() courses!: types.CoursesByAccessMode;
  @Prop() activeTab!: string;
  @Prop({ default: true }) showPercentage!: boolean;

  T = T;
  time = time;
  showTab = this.activeTab;

  getTabName(timeType: string): string {
    if (timeType === 'current') return T.courseListCurrentCourses;
    if (timeType === 'past') return T.courseListPastCourses;
    return '';
  }
}
</script>
