<template>
  <div class="omegaup-course-assignmentdetails card" v-show="show">
    <slot name="page-header">
      <div class="card-header">
        <h1>{{ T.wordsContentEdit }} {{ assignment.name }}</h1>
      </div>
    </slot>
    <div class="card-body">
      <form class="form schedule" v-on:submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-4">
            <label
              >{{ T.wordsTitle }}
              <input
                ref="name"
                class="form-control name"
                v-bind:class="{ 'is-invalid': invalidParameterName === 'name' }"
                size="30"
                type="text"
                v-model="name"
                required
            /></label>
          </div>
          <div class="form-group col-md-4">
            <label
              >{{ T.courseNewFormShortTitle_alias_ }}
              <font-awesome-icon
                v-bind:title="T.courseAssignmentNewFormShortTitle_alias_Desc"
                icon="info-circle" />
              <input
                class="form-control alias"
                v-bind:class="{
                  'is-invalid': invalidParameterName === 'alias',
                }"
                type="text"
                v-bind:disabled="update"
                v-model="alias"
                required
            /></label>
          </div>
          <div class="form-group col-md-4">
            <label
              >{{ T.wordsContentType }}
              <font-awesome-icon
                v-bind:title="T.courseContentNewFormTypeDesc"
                icon="info-circle"
              />
              <select
                class="form-control"
                v-bind:class="{
                  'is-invalid': invalidParameterName === 'assignment_type',
                }"
                v-model="assignmentType"
                required
              >
                <option value="lesson">
                  {{ T.wordsLesson }}
                </option>
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
          <div class="form-group col-md-4">
            <label
              >{{ T.courseNewFormStartDate }}
              <font-awesome-icon
                v-bind:title="T.courseAssignmentNewFormStartDateDesc"
                icon="info-circle" />
              <omegaup-datetimepicker
                v-bind:enabled="!assignment.has_runs"
                v-model="startTime"
                v-bind:finish="finishTimeCourse"
                v-bind:start="startTimeCourse"
                v-bind:is-invalid="invalidParameterName === 'start_time'"
              ></omegaup-datetimepicker
            ></label>
          </div>
          <div class="form-group col-md-4">
            <span class="faux-label"
              >{{ T.courseNewFormUnlimitedDuration }}
              <font-awesome-icon
                v-bind:title="T.courseNewFormUnlimitedDurationDesc"
                icon="info-circle"
              />
            </span>
            <div
              class="form-control container-fluid"
              v-bind:class="{
                'is-invalid': invalidParameterName === 'unlimited_duration',
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
          <div class="form-group col-md-4">
            <label
              >{{ T.courseNewFormEndDate }}
              <font-awesome-icon
                v-bind:title="T.courseAssignmentNewFormEndDateDesc"
                icon="info-circle" />
              <omegaup-datetimepicker
                v-bind:enabled="!unlimitedDuration"
                v-bind:readonly="false"
                v-model="finishTime"
                v-bind:finish="finishTimeCourse"
                v-bind:start="startTimeCourse"
                v-bind:is-invalid="invalidParameterName === 'finish_time'"
              ></omegaup-datetimepicker
            ></label>
          </div>
        </div>
        <div class="row">
          <div class="form-group container-fluid">
            <label
              >{{ T.courseNewFormDescription }}
              <textarea
                class="form-control"
                v-bind:class="{
                  'is-invalid': invalidParameterName === 'description',
                }"
                cols="30"
                rows="5"
                v-model="description"
                required
              ></textarea>
            </label>
          </div>
        </div>
        <template v-if="shouldAddProblems">
          <omegaup-course-scheduled-problem-list
            ref="scheduled-problem-list"
            v-if="assignmentFormMode === AssignmentFormMode.New"
            v-bind:assignment-problems="assignmentProblems"
            v-bind:tagged-problems="taggedProblems"
            v-bind:selected-assignment="assignment"
            v-on:emit-tags="(tags) => $emit('tags-problems', tags)"
          ></omegaup-course-scheduled-problem-list>
          <omegaup-course-problem-list
            v-else
            v-bind:assignment-problems="assignmentProblems"
            v-bind:tagged-problems="taggedProblems"
            v-bind:selected-assignment="assignment"
            v-bind:assignment-form-mode.sync="assignmentFormMode"
            v-on:save-problem="
              (assignment, problem) => $emit('add-problem', assignment, problem)
            "
            v-on:emit-remove-problem="
              (assignment, problem) =>
                $emit('remove-problem', assignment, problem)
            "
            v-on:emit-select-assignment="
              (assignment) => $emit('select-assignment', assignment)
            "
            v-on:emit-sort="
              (assignmentAlias, problemsAlias) =>
                $emit('sort-problems', assignmentAlias, problemsAlias)
            "
            v-on:emit-tags="(tags) => $emit('tags-problems', tags)"
            v-on:change-alias="
              (addProblemComponent, newProblemAlias) =>
                $emit('get-versions', newProblemAlias, addProblemComponent)
            "
          ></omegaup-course-problem-list>
        </template>
        <div class="form-group text-right mt-3">
          <button
            data-schedule-assignment
            class="btn btn-primary submit mr-2"
            type="submit"
          >
            <template v-if="update">
              {{ T.courseAssignmentNewFormUpdate }}
            </template>
            <template v-else>
              {{ T.courseAssignmentNewFormSchedule }}
            </template>
          </button>
          <slot name="cancel-button">
            <button
              class="btn btn-secondary"
              type="reset"
              v-on:click.prevent="onCancel"
            >
              {{ T.wordsBack }}
            </button>
          </slot>
        </div>
      </form>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.omegaup-course-assignmentdetails .form-group > label {
  width: 100%;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch, Emit, Ref } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import course_ProblemList from './ProblemList.vue';
import course_ScheduledProblemList from './ScheduledProblemList.vue';
import DateTimePicker from '../DateTimePicker.vue';

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
    'omegaup-datetimepicker': DateTimePicker,
    'omegaup-course-problem-list': course_ProblemList,
    'omegaup-course-scheduled-problem-list': course_ScheduledProblemList,
  },
})
export default class CourseAssignmentDetails extends Vue {
  @Ref('scheduled-problem-list')
  readonly scheduledProblemList!: course_ScheduledProblemList;
  @Prop({
    default: omegaup.AssignmentFormMode.Default,
  })
  assignmentFormMode!: omegaup.AssignmentFormMode;
  @Prop() assignment!: types.CourseAssignment;
  @Prop() finishTimeCourse!: Date;
  @Prop() startTimeCourse!: Date;
  @Prop() assignmentProblems!: types.ProblemsetProblem[];
  @Prop() taggedProblems!: omegaup.Problem[];
  @Prop({ default: true }) shouldAddProblems!: boolean;
  @Prop({ default: false }) unlimitedDurationCourse!: boolean;
  @Prop({ default: '' }) invalidParameterName!: string;

  T = T;
  AssignmentFormMode = omegaup.AssignmentFormMode;
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

  get show(): boolean {
    switch (this.assignmentFormMode) {
      case omegaup.AssignmentFormMode.New:
        return true;
      case omegaup.AssignmentFormMode.Edit:
        return true;
      case omegaup.AssignmentFormMode.Default:
        return false;
      default:
        return false;
    }
  }

  get update(): boolean {
    switch (this.assignmentFormMode) {
      case omegaup.AssignmentFormMode.New:
        return false;
      case omegaup.AssignmentFormMode.Edit:
        return true;
      case omegaup.AssignmentFormMode.Default:
        return true;
      default:
        return true;
    }
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

  @Watch('show')
  onShowChanged(): void {
    this.reset();
  }

  @Emit('cancel')
  onCancel(): void {
    this.reset();
  }

  onSubmit(): void {
    this.$emit('submit', this, this.scheduledProblemList?.problems ?? []);
  }
}
</script>
