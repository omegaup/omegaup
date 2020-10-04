<template>
  <div v-show="show" class="omegaup-course-assignmentdetails card">
    <slot name="page-header">
      <div class="card-header">
        <h1>{{ T.wordsContentEdit }} {{ assignment.name }}</h1>
      </div>
    </slot>
    <div class="card-body">
      <form class="form schedule" @submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-4">
            <label
              >{{ T.wordsTitle }}
              <input
                ref="name"
                v-model="name"
                class="form-control name"
                :class="{ 'is-invalid': invalidParameterName === 'name' }"
                size="30"
                type="text"
                required
            /></label>
          </div>
          <div class="form-group col-md-4">
            <label
              >{{ T.courseNewFormShortTitle_alias_ }}
              <font-awesome-icon
                :title="T.courseAssignmentNewFormShortTitle_alias_Desc"
                icon="info-circle" />
              <input
                v-model="alias"
                class="form-control alias"
                :class="{
                  'is-invalid': invalidParameterName === 'alias',
                }"
                type="text"
                :disabled="update"
                required
            /></label>
          </div>
          <div class="form-group col-md-4">
            <label
              >{{ T.wordsContentType }}
              <font-awesome-icon
                :title="T.courseContentNewFormTypeDesc"
                icon="info-circle"
              />
              <select
                v-model="assignmentType"
                class="form-control"
                :class="{
                  'is-invalid': invalidParameterName === 'assignment_type',
                }"
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
                :title="T.courseAssignmentNewFormStartDateDesc"
                icon="info-circle" />
              <omegaup-datetimepicker
                v-model="startTime"
                :enabled="!assignment.has_runs"
                :finish="finishTimeCourse"
                :start="startTimeCourse"
                :is-invalid="invalidParameterName === 'start_time'"
              ></omegaup-datetimepicker
            ></label>
          </div>
          <div class="form-group col-md-4">
            <span class="faux-label"
              >{{ T.courseNewFormUnlimitedDuration }}
              <font-awesome-icon
                :title="T.courseNewFormUnlimitedDurationDesc"
                icon="info-circle"
              />
            </span>
            <div
              class="form-control container-fluid"
              :class="{
                'is-invalid': invalidParameterName === 'unlimited_duration',
              }"
            >
              <label class="radio-inline"
                ><input
                  v-model="unlimitedDuration"
                  type="radio"
                  :value="true"
                  :disabled="!unlimitedDurationCourse"
                />{{ T.wordsYes }}</label
              >
              <label class="radio-inline"
                ><input
                  v-model="unlimitedDuration"
                  type="radio"
                  :value="false"
                  :disabled="!unlimitedDurationCourse"
                />{{ T.wordsNo }}</label
              >
            </div>
          </div>
          <div class="form-group col-md-4">
            <label
              >{{ T.courseNewFormEndDate }}
              <font-awesome-icon
                :title="T.courseAssignmentNewFormEndDateDesc"
                icon="info-circle" />
              <omegaup-datetimepicker
                v-model="finishTime"
                :enabled="!unlimitedDuration"
                :readonly="false"
                :finish="finishTimeCourse"
                :start="startTimeCourse"
                :is-invalid="invalidParameterName === 'finish_time'"
              ></omegaup-datetimepicker
            ></label>
          </div>
        </div>
        <div class="row">
          <div class="form-group container-fluid">
            <label
              >{{ T.courseNewFormDescription }}
              <textarea
                v-model="description"
                class="form-control"
                :class="{
                  'is-invalid': invalidParameterName === 'description',
                }"
                cols="30"
                rows="5"
                required
              ></textarea>
            </label>
          </div>
        </div>
        <template v-if="shouldAddProblems">
          <omegaup-course-scheduled-problem-list
            v-if="assignmentFormMode === AssignmentFormMode.New"
            ref="scheduled-problem-list"
            :assignment-problems="assignmentProblems"
            :tagged-problems="taggedProblems"
            :selected-assignment="assignment"
            @emit-tags="(tags) => $emit('tags-problems', tags)"
          ></omegaup-course-scheduled-problem-list>
          <omegaup-course-problem-list
            v-else
            :assignment-problems="assignmentProblems"
            :tagged-problems="taggedProblems"
            :selected-assignment="assignment"
            :assignment-form-mode.sync="assignmentFormMode"
            @save-problem="
              (assignment, problem) => $emit('add-problem', assignment, problem)
            "
            @emit-remove-problem="
              (assignment, problem) =>
                $emit('remove-problem', assignment, problem)
            "
            @emit-select-assignment="
              (assignment) => $emit('select-assignment', assignment)
            "
            @emit-sort="
              (assignmentAlias, problemsAlias) =>
                $emit('sort-problems', assignmentAlias, problemsAlias)
            "
            @emit-tags="(tags) => $emit('tags-problems', tags)"
            @change-alias="
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
              @click.prevent="onCancel"
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
