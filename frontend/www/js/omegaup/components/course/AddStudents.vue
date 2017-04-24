<template>
  <div class="omegaup-course-addstudent panel">
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onAddStudent">
        <div class="form-group">
          <label for="member-username">{{ T.wordsStudent }}</label> <span aria-hidden="true"
               class="glyphicon glyphicon-info-sign"
               data-placement="top"
               data-toggle="tooltip"
               v-bind:title="T.courseEditAddStudentsTooltip"></span> <input autocomplete="off"
               class="form-control typeahead"
               size="20"
               type="text">
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary"
               type="submit">{{ T.wordsAddStudent }}</button> <button class="btn btn-secondary"
               type="reset"
               v-on:click.prevent="onCancel">{{ T.wordsCancel }}</button>
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

export default {
  props: {
    T: Object,
    courseAlias: String,
    students: Array,
  },
  data: function() {
    return {
      studentUsername: '',
    };
  },
  mounted: function() {
    var self = this;
    UI.userTypeahead($('input.typeahead', $(this.$el)), function(event, item) {
      self.studentUsername = item.value;
    });
  },
  methods: {
    onAddStudent: function() {
      this.$emit('add-student', this.studentUsername);
    },
    onCancel: function() { this.$emit('cancel');},
    onRemove: function(student) { this.$emit('remove', student);},
    reset: function() { this.studentUsername = '';},
    studentProgressUrl: function(student) {
      return '/course/' + this.courseAlias + '/student/' + student.username +
             '/';
    },
  },
};
</script>

<style>
.omegaup-course-addstudent th.align-right {
  text-align: right;
}
</style>
