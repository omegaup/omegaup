<template>
  <div class="omegaup-course-assignmentdetails panel"
       v-show="show">
    <div class="panel-body">
      <form class="form schedule"
            v-on:submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-8">
            <label>{{ T.wordsTitle }} <input class="form-control name"
                   size="30"
                   type="text"
                   v-model="name"></label>
          </div>
          <div class="form-group col-md-4">
            <label>{{ T.courseNewFormShortTitle_alias_ }} <span aria-hidden="true"
                  class="glyphicon glyphicon-info-sign"
                  data-placement="top"
                  data-toggle="tooltip"
                  v-bind:title="T.courseAssignmentNewFormShortTitle_alias_Desc"></span>
                  <input class="form-control alias"
                   type="text"
                   v-bind:disabled="update"
                   v-model="alias"></label>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-4">
            <label>{{ T.courseNewFormStartDate }} <span aria-hidden="true"
                  class="glyphicon glyphicon-info-sign"
                  data-placement="top"
                  data-toggle="tooltip"
                  v-bind:title="T.courseAssignmentNewFormStartDateDesc"></span>
                  <omegaup-datetimepicker v-bind:enabled="!assignment.has_runs"
                                    v-model="startTime"></omegaup-datetimepicker></label>
          </div>
          <div class="form-group col-md-4">
            <label>{{ T.courseNewFormEndDate }} <span aria-hidden="true"
                  class="glyphicon glyphicon-info-sign"
                  data-placement="top"
                  data-toggle="tooltip"
                  v-bind:title="T.courseAssignmentNewFormEndDateDesc"></span>
                  <omegaup-datetimepicker v-model="finishTime"></omegaup-datetimepicker></label>
          </div>
          <div class="form-group col-md-4">
            <label>{{ T.courseAssignmentNewFormType }} <span aria-hidden="true"
                  class="glyphicon glyphicon-info-sign"
                  data-placement="top"
                  data-toggle="tooltip"
                  v-bind:title="T.courseAssignmentNewFormTypeDesc"></span> <select class=
                  "form-control"
                    v-model="assignmentType">
              <option value="homework">
                {{ T.wordsHomework }}
              </option>
              <option value="test">
                {{ T.wordsTest }}
              </option>
            </select></label>
          </div>
        </div>
        <div class="row">
          <div class="form-group container-fluid">
            <label>{{ T.courseNewFormDescription }}
            <textarea class="form-control"
                      cols="30"
                      rows="5"
                      v-model="description"></textarea></label>
          </div>
        </div><!-- Problem list editor -->
        <div class="panel-footer"
             v-show="!update">
          <form>
            <div class="row">
              <div class="row">
                <div class="form-group col-md-12">
                  <label>{{ T.wordsProblem }} <input autocomplete="off"
                         class="typeahead form-control problems-dropdown"
                         v-model="currentProblem.alias"></label>
                  <p class="help-block">{{ T.courseAddProblemsAssignmentsDesc }}</p>
                </div>
              </div>
              <div class="form-group pull-right">
                <button class="btn btn-primary"
                     type="submit"
                     v-bind:disabled="currentProblem.alias.length == 0"
                     v-on:click.prevent="onAddProblem">{{ T.courseEditAddProblems }}</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row">
          <div class="table-body"
               v-if="assignmentProblems.length == 0">
            <div class="empty-category">
              {{ T.courseAssignmentProblemsEmpty }}
            </div>
          </div>
          <table class="table table-striped"
                 v-else="">
            <thead>
              <tr>
                <th>{{ T.contestAddproblemProblemOrder }}</th>
                <th>{{ T.contestAddproblemProblemName }}</th>
                <th>{{ T.contestAddproblemProblemRemove }}</th>
              </tr>
            </thead>
            <tbody v-sortable="{ onUpdate: sort }">
              <tr v-bind:key="problem.letter"
                  v-for="problem in assignmentProblems">
                <td>
                  <a v-bind:title="T.courseAssignmentProblemReorder"><span aria-hidden="true"
                        class="glyphicon glyphicon-move handle"></span></a>
                </td>
                <td>{{ problem.title }}</td>
                <td class="button-column">
                  <a v-bind:title="T.courseAssignmentProblemRemove"
                      v-on:click="onProblemRemove(problem)"><span aria-hidden="true"
                        class="glyphicon glyphicon-remove"></span></a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary submit"
               type="submit">
          <template v-if="update">
            {{ T.courseAssignmentNewFormUpdate }}
          </template>
          <template v-else="">
            {{ T.courseAssignmentNewFormSchedule }}
          </template></button> <button class="btn btn-secondary"
               type="reset"
               v-on:click.prevent="onCancel">{{ T.wordsCancel }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import DateTimePicker from '../DateTimePicker.vue';
import UI from '../../ui.js';

export default {
  props: {
    T: Object,
    update: Boolean,
    assignment: Object,
    assignmentProblems: Array,
    show: {
      type: Boolean,
      'default': false,
    },
  },
  data: function() {
    return {
      alias: this.assignment.alias,
      assignmentType: this.assignment.assignment_type || 'homework',
      description: this.assignment.description,
      finishTime: this.assignment.finish_time || new Date(),
      name: this.assignment.name,
      startTime: this.assignment.start_time || new Date(),
      currentProblem: {alias: '', title: ''},
    };
  },
  watch: {
    assignment: function(val) {
      this.reset();
      this.$emit('assignment', val);
    },
  },
  mounted: function() {
    var self = this;
    UI.problemTypeahead($('input.problems-dropdown', $(this.$el)),
                        function(event, item) { self.currentProblem = item; });
  },
  methods: {
    reset: function() {
      this.alias = this.assignment.alias;
      this.assignmentType = this.assignment.assignment_type || 'homework';
      this.description = this.assignment.description;
      this.finishTime = this.assignment.finish_time || new Date();
      this.name = this.assignment.name;
      this.startTime = this.assignment.start_time || new Date();
      this.currentProblem = {alias: '', title: ''};
    },
    onSubmit: function() { this.$emit('submit', this);},
    onCancel: function() {
      this.reset();
      this.$emit('cancel');
    },
    sort: function(event) {
      this.assignmentProblems.splice(
          event.newIndex, 0,
          this.assignmentProblems.splice(event.oldIndex, 1)[0]);
      this.$emit('sort', this.assignment, this.assignmentProblems);
    },
    onProblemRemove: function(problem) {
      this.$emit('problemRemove', this.assignment, problem);
    },
    onAddProblem: function() { this.$emit('addProblem', this.currentProblem);}
  },
  components: {
    'omegaup-datetimepicker': DateTimePicker,
  },
};
</script>

<style>
.omegaup-course-assignmentdetails .form-group>label {
  width: 100%;
}
</style>
