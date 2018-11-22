<template>
  <div class="omegaup-course-addstudent panel">
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onSubmit">
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
                    v-on:click="onRemove(student)">×</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import UI from '../../ui.js';
import Autocomplete from '../Autocomplete.vue';

export default {
  props: {
    T: Object,
    courseAlias: String,
    students: Array,
  },
  data: function() {
    return {
      studentUsername: '',
      participant: '',
      participants: '',
      UI: UI,
    };
  },
  methods: {
    onSubmit: function() { this.$emit('add-student', this);},
    onRemove: function(student) { this.$emit('remove-student', student);},
    studentProgressUrl: function(student) {
      return '/course/' + this.courseAlias + '/student/' + student.username +
             '/';
    },
  },
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
};
</script>

<style>
.omegaup-course-addstudent th.align-right {
  text-align: right;
}
</style>
