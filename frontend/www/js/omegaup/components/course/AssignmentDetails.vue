<template>
  <div class="omegaup-course-assignmentdetails panel" v-show="show">
    <div class="panel-body">
      <form class="form schedule" v-on:submit.prevent="onSubmit">
        <div class="row">
          <div
            class="form-group col-md-4"
            v-bind:class="{ 'has-error': invalidParameterName === 'name' }"
          >
            <label
              >{{ T.wordsTitle }}
              <input
                class="form-control name"
                size="30"
                type="text"
                v-model="name"
                required
            /></label>
          </div>
          <div
            class="form-group col-md-4"
            v-bind:class="{ 'has-error': invalidParameterName === 'alias' }"
          >
            <label
              >{{ T.courseNewFormShortTitle_alias_ }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseAssignmentNewFormShortTitle_alias_Desc"
              ></span>
              <input
                class="form-control alias"
                type="text"
                v-bind:disabled="update"
                v-model="alias"
                required
            /></label>
          </div>
          <div
            class="form-group col-md-4"
            v-bind:class="{
              'has-error': invalidParameterName === 'assignment_type',
            }"
          >
            <label
              >{{ T.courseAssignmentNewFormType }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseAssignmentNewFormTypeDesc"
              ></span>
              <select class="form-control" v-model="assignmentType" required>
                <option value="homework">
                  {{ T.wordsHomework }}
                </option>
                <option value="test">
                  {{ T.wordsExam }}
                </option>
              </select></label
            >
          </div>
        </div>
        <div class="row">
          <div
            class="form-group col-md-4"
            v-bind:class="{
              'has-error': invalidParameterName === 'start_time',
            }"
          >
            <label
              >{{ T.courseNewFormStartDate }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseAssignmentNewFormStartDateDesc"
              ></span>
              <omegaup-datetimepicker
                v-bind:enabled="!assignment.has_runs"
                v-model="startTime"
                v-bind:finish="finishTimeCourse"
                v-bind:start="startTimeCourse"
              ></omegaup-datetimepicker
            ></label>
          </div>
          <div class="form-group col-md-4">
            <span class="faux-label"
              >{{ T.courseNewFormUnlimitedDuration }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseNewFormUnlimitedDurationDesc"
              ></span
            ></span>
            <div
              class="form-control container-fluid"
              v-bind:class="{
                'has-error': invalidParameterName === 'unlimited_duration',
              }"
            >
              <label class="radio-inline"
                ><input
                  type="radio"
                  v-bind:value="true"
                  v-model="unlimitedDuration"
                  v-bind:disabled="!unlimitedDurationCourse"
                />{{ T.wordsYes }}</label
              >
              <label class="radio-inline"
                ><input
                  type="radio"
                  v-bind:value="false"
                  v-model="unlimitedDuration"
                  v-bind:disabled="!unlimitedDurationCourse"
                />{{ T.wordsNo }}</label
              >
            </div>
          </div>
          <div
            class="form-group col-md-4"
            v-bind:class="{
              'has-error': invalidParameterName === 'finish_time',
            }"
          >
            <label
              >{{ T.courseNewFormEndDate }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseAssignmentNewFormEndDateDesc"
              ></span>
              <omegaup-datetimepicker
                v-bind:enabled="!unlimitedDuration"
                v-bind:readonly="false"
                v-model="finishTime"
                v-bind:finish="finishTimeCourse"
                v-bind:start="startTimeCourse"
              ></omegaup-datetimepicker
            ></label>
          </div>
        </div>
        <div class="row">
          <div
            class="form-group container-fluid"
            v-bind:class="{
              'has-error': invalidParameterName === 'description',
            }"
          >
            <label
              >{{ T.courseNewFormDescription }}
              <textarea
                class="form-control"
                cols="30"
                rows="5"
                v-model="description"
                required
              ></textarea>
            </label>
          </div>
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary submit" type="submit">
            <template v-if="update">
              {{ T.courseAssignmentNewFormUpdate }}
            </template>
            <template v-else="">
              {{ T.courseAssignmentNewFormSchedule }}
            </template>
          </button>
          <button
            class="btn btn-secondary"
            type="reset"
            v-on:click.prevent="onCancel"
          >
            {{ T.wordsCancel }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style>
.omegaup-course-assignmentdetails .form-group > label {
  width: 100%;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch, Emit } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import DateTimePicker from '../DateTimePicker.vue';

@Component({
  components: {
    'omegaup-datetimepicker': DateTimePicker,
  },
})
export default class CourseAssignmentDetails extends Vue {
  @Prop() update!: boolean;
  @Prop() assignment!: omegaup.Assignment;
  @Prop({ default: false }) show!: boolean;
  @Prop() finishTimeCourse!: Date;
  @Prop() startTimeCourse!: Date;
  @Prop({ default: false }) unlimitedDurationCourse!: boolean;
  @Prop({ default: '' }) invalidParameterName!: string;

  T = T;
  alias = this.assignment.alias || '';
  assignmentType = this.assignment.assignment_type || 'homework';
  description = this.assignment.description || '';
  name = this.assignment.name || '';
  startTime = this.assignment.start_time || new Date();
  finishTime = this.assignment.finish_time || new Date();
  unlimitedDuration = !this.assignment.finish_time;

  @Watch('assignment')
  onAssignmentChange() {
    this.reset();
  }

  reset(): void {
    this.alias = this.assignment.alias;
    this.assignmentType = this.assignment.assignment_type || 'homework';
    this.description = this.assignment.description;
    this.finishTime = this.assignment.finish_time || new Date();
    this.name = this.assignment.name;
    this.startTime = this.assignment.start_time || new Date();
    this.unlimitedDuration = !this.assignment.finish_time;
  }

  @Emit('cancel')
  onCancel(): void {
    this.reset();
  }

  onSubmit(): void {
    this.$emit('submit', this);
  }
}
</script>
