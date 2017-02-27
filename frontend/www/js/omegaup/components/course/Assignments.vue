<template>
  <div class="omegaup-course-assignments panel">
    <div class="panel-body">
      <form class="form" v-on:submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-8">
            <label>
              {{ T.wordsTitle }}
              <input v-model="name" type="text" size="30" class="form-control">
            </label>
          </div>

          <div class="form-group col-md-4">
            <label for="alias">
              {{ T.courseNewFormShortTitle_alias_ }}
              <span data-toggle="tooltip" data-placement="top"
                  v-bind:title="T.courseAssignmentNewFormShortTitle_alias_Desc"
                  class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
              <input v-model="alias" type="text" class="form-control"
                  v-bind:disabled="update">
            </label>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-4">
            <label>{{ T.courseNewFormStartDate }}
              <span data-toggle="tooltip" data-placement="top"
                  v-bind:title="T.courseAssignmentNewFormStartDateDesc"
                  class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
              <omegaup-datetimepicker v-model="startTime"></omegaup-datetimepicker>
            </label>
          </div>

          <div class="form-group col-md-4">
            <label>{{ T.courseNewFormEndDate }}
              <span data-toggle="tooltip" data-placement="top"
                  v-bind:title="T.courseAssignmentNewFormEndDateDesc"
                  class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
              <omegaup-datetimepicker v-model="finishTime"></omegaup-datetimepicker>
            </label>
          </div>

          <div class="form-group col-md-4">
            <label>
              {{ T.courseAssignmentNewFormType }}
              <span data-toggle="tooltip" data-placement="top"
                  v-bind:title="T.courseAssignmentNewFormTypeDesc"
                  class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
              <select class="form-control" v-model="assignmentType">
                <option value="homework">{{ T.wordsHomework }}</option>
                <option value="test">{{ T.wordsTest }}</option>
              </select>
            </label>
          </div>
        </div>

        <div class="row">
          <div class="form-group container">
            <label>
              {{ T.courseNewFormDescription }}</label>
              <textarea cols="30" rows="5" class="form-control" v-model="description"></textarea>
            </label>
          </div>
        </div>

        <div class="form-group pull-right">
          <button type="submit" class="btn btn-primary">
            <template v-if="update">{{ T.courseAssignmentNewFormUpdate }}</template>
            <template v-else>{{ T.courseAssignmentNewFormSchedule }}</template>
          </button>
          <button v-on:click.prevent="reset" type="reset" class="btn btn-secondary">
            {{ T.wordsCancel }}
          </button>
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
    assignment: function(val) {
      this.reset();
    },
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
    onSubmit: function() {
      this.$emit('submit', this);
    },
  },
  components: {
    'omegaup-datetimepicker': DateTimePicker,
  },
};
</script>

<style>
.omegaup-course-assignments .form-group>label {
  width: 100%;
}
</style>
