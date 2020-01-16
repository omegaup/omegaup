<template>
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
            <th colspan="2">{{ T.wordsActions }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="course in courses">
            <td>
              <a v-bind:href="`/course/${course.alias}/`">{{ course.name }}</a>
            </td>
            <td>{{ UI.formatDate(course.start_time) }}</td>
            <td>{{ UI.formatDate(course.finish_time) }}</td>
            <td>{{ course.counts.homework }}</td>
            <td>{{ course.counts.test }}</td>
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
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

@Component
export default class CourseFilteredList extends Vue {
  @Prop() courses!: omegaup.Course[];

  T = T;
  UI = UI;
}
</script>
