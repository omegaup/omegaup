<template>
  <div class="omegaup-course-assignmentdetails panel"
       v-show="show">
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-8">
            <label>{{ T.wordsTitle }} <input class="form-control"
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
                  <input class="form-control"
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
                  <omegaup-datetimepicker v-model="startTime"></omegaup-datetimepicker></label>
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
          <div class="form-group container">
            <label>{{ T.courseNewFormDescription }}
            <textarea class="form-control"
                      cols="30"
                      rows="5"
                      v-model="description"></textarea></label>
          </div>
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary"
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

export default {
  props: {
    T: Object,
    update: Boolean,
    assignment: Object,
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
    };
  },
  watch: {
    assignment: function(val) { this.reset();},
  },
  methods: {
    reset: function() {
      this.alias = this.assignment.alias;
      this.assignmentType = this.assignment.assignment_type || 'homework';
      this.description = this.assignment.description;
      this.finishTime = this.assignment.finish_time || new Date();
      this.name = this.assignment.name;
      this.startTime = this.assignment.start_time || new Date();
    },
    onSubmit: function() { this.$emit('submit', this);},
    onCancel: function() {
      this.reset();
      this.$emit('cancel');
    },
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
