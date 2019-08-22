<template>
  <div class="omegaup-course-addstudent panel">
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="$emit('add-student', {participant, participants})">
        <div class="form-group">
          <label>{{ T.wordsStudent }}</label> <span aria-hidden="true"
               class="glyphicon glyphicon-info-sign"
               data-placement="top"
               data-toggle="tooltip"
               v-bind:title="T.courseEditAddStudentsTooltip"></span>
               <omegaup-autocomplete v-bind:init="el =&gt; UI.userTypeahead(el)"
               v-model="participant"></omegaup-autocomplete>
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary"
               type="submit">{{ T.wordsAddStudent }}</button>
        </div>
        <div class="form-group">
          <label>{{T.wordsMultipleUser}}</label>
          <textarea class="form-control pariticipants"
               rows="4"
               v-model="participants"></textarea>
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary user-add-bulk"
               type="submit">{{ T.wordsAddStudents }}</button>
        </div>
      </form>
      <div v-if="students.length == 0">
        <div class="empty-category">
          {{ T.courseStudentsEmpty }}
        </div>
      </div>
      <table class="table table-striped table-over"
             v-else="">
        <thead>
          <tr>
            <th>{{ T.wordsUser }}</th>
            <th class="align-right">{{ T.contestEditRegisteredAdminDelete }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students">
            <td>
              <a v-bind:href="studentProgressUrl(student)">{{ student.name || student.username
              }}</a>
            </td>
            <td><button class="close"
                    type="button"
                    v-on:click="$emit('remove-student', student)">×</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style>
.omegaup-course-addstudent th.align-right {
  text-align: right;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';
import Autocomplete from '../Autocomplete.vue';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class CourseAddStudents extends Vue {
  @Prop() courseAlias!: string;
  @Prop() students!: omegaup.CourseStudent[];

  T = T;
  UI = UI;
  studentUsername = '';
  participant = '';
  participants = '';

  studentProgressUrl(student: omegaup.CourseStudent): string {
    return `/course/${this.courseAlias}/student/${student.username}/`;
  }
}

</script>
