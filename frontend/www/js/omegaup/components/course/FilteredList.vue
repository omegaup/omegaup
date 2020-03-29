<template>
  <div class="panel-body tab-container">
    <ul class="nav nav-tabs">
      <li
        class="nav-item"
        v-bind:class="{ active: activeTab === filteredCourses.timeType }"
        v-if="filteredCourses.courses.length > 0"
        v-on:click="showTab = filteredCourses.timeType"
        v-for="filteredCourses in courses.filteredCourses"
      >
        <a data-toggle="tab">{{ filteredCourses.tabName }}</a>
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
                  <th>{{ T.wordsStartTime }}</th>
                  <th>{{ T.wordsEndTime }}</th>
                  <th>{{ T.wordsNumHomeworks }}</th>
                  <th>{{ T.wordsNumTests }}</th>
                  <th colspan="2" v-if="courses.accessMode === 'admin'">
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
                  <template v-if="courses.accessMode === 'admin'">
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
import { omegaup } from '../../omegaup';
import T from '../../lang';
import UI from '../../ui.js';

@Component
export default class CourseFilteredList extends Vue {
  @Prop() courses!: omegaup.Course[];
  @Prop() activeTab!: string;

  T = T;
  UI = UI;
  showTab = this.activeTab;
}
</script>
