<template>
  <div>
    <ul class="nav nav-tabs">
      <template v-for="filteredCourses in courses.filteredCourses">
        <li
          v-if="filteredCourses.courses"
          :key="filteredCourses.timeType"
          class="nav-item"
          @click="showTab = filteredCourses.timeType"
        >
          <a
            data-toggle="tab"
            class="nav-link"
            href="#"
            :class="{ active: activeTab === filteredCourses.timeType }"
            >{{ getTabName(filteredCourses.timeType) }}</a
          >
        </li>
      </template>
    </ul>

    <div class="tab-content">
      <template v-for="filteredCourses in courses.filteredCourses">
        <div
          v-if="showTab === filteredCourses.timeType"
          :key="filteredCourses.timeType"
          class="tab-pane active table-responsive"
        >
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th class="text-center">{{ T.wordsName }}</th>
                <th v-if="showPercentage" class="text-center">
                  {{ T.wordsCompletedPercentage }}
                </th>
                <th class="text-center">{{ T.wordsDueDate }}</th>
                <th class="text-center">{{ T.wordsNumHomeworks }}</th>
                <th class="text-center">{{ T.wordsNumTests }}</th>
                <th
                  v-if="courses.accessMode === 'admin'"
                  class="text-center"
                  colspan="3"
                >
                  {{ T.wordsActions }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="course in filteredCourses.courses" :key="course.alias">
                <td>
                  <a :href="`/course/${course.alias}/`">{{ course.name }}</a>
                </td>
                <td v-if="showPercentage" class="text-center">
                  {{ `${course.progress}%` }}
                </td>
                <td class="text-center">
                  {{
                    course.finish_time
                      ? time.formatDate(course.finish_time)
                      : T.wordsUnlimitedDuration
                  }}
                </td>
                <td class="text-center">
                  {{
                    course.counts.homework
                      ? course.counts.homework
                      : T.wordsNotApplicable
                  }}
                </td>
                <td class="text-center">
                  {{
                    course.counts.test
                      ? course.counts.test
                      : T.wordsNotApplicable
                  }}
                </td>
                <template v-if="courses.accessMode === 'admin'">
                  <td>
                    <a :href="`/course/${course.alias}/edit/`">
                      <font-awesome-icon
                        icon="edit"
                        :title="T.omegaupTitleCourseEdit"
                      />
                    </a>
                  </td>
                  <td>
                    <a href="`/course/${course.alias}/list/`">
                      <font-awesome-icon
                        icon="list-alt"
                        :title="T.courseListSubmissionsByGroup"
                      />
                    </a>
                  </td>
                  <td>
                    <a :href="`/course/${course.alias}/activity/`">
                      <font-awesome-icon
                        icon="clock"
                        :title="T.activityReport"
                      />
                    </a>
                  </td>
                </template>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>
  </div>
</template>

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

<style>
.nav-tabs .nav-item {
  margin-bottom: -2px;
}
</style>
