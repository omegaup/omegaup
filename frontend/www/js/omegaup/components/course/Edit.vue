<template>
  <div class="course-edit">
    <div class="page-header">
      <h1>
        <span>{{ T.wordsEditCourse }} {{ data.course.name }}</span>
        <small>
          &ndash;
          <a v-bind:href="courseURL">
            {{ T.courseEditGoToCourse }}
          </a>
        </small>
      </h1>
    </div>
    <ul class="nav nav-pills">
      <li class="nav-item" role="presentation">
        <a
          href="#course"
          class="nav-link"
          data-tab-course
          v-on:click="showTab = 'course'"
          v-bind:class="{ active: showTab === 'course' }"
          >{{ T.courseEdit }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#content"
          class="nav-link"
          data-tab-content
          v-on:click="onSelectAssignmentTab"
          v-bind:class="{ active: showTab === 'content' }"
          >{{ T.wordsContent }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#admission-mode"
          class="nav-link"
          data-tab-admission-mode
          v-on:click="showTab = 'admission-mode'"
          v-bind:class="{ active: showTab === 'admission-mode' }"
          >{{ T.contestNewFormAdmissionMode }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#students"
          class="nav-link"
          data-tab-students
          v-on:click="showTab = 'students'"
          v-bind:class="{ active: showTab === 'students' }"
          >{{ T.courseEditStudents }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#admins"
          class="nav-link"
          data-tab-admins
          v-on:click="showTab = 'admins'"
          v-bind:class="{ active: showTab === 'admins' }"
          >{{ T.courseEditAdmins }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#clone"
          class="nav-link"
          data-tab-clone
          v-on:click="showTab = 'clone'"
          v-bind:class="{ active: showTab === 'clone' }"
          >{{ T.courseEditClone }}</a
        >
      </li>
    </ul>

    <div class="tab-content card">
      <div class="tab-pane active" role="tabpanel" v-if="showTab === 'course'">
        <omegaup-course-form
          v-bind:update="true"
          v-bind:course="data.course"
          v-on:emit-cancel="onCancel"
          v-on:submit="
            (formComponent) => $emit('submit-edit-course', formComponent)
          "
        ></omegaup-course-form>
      </div>

      <div
        data-content-tab
        class="tab-pane active"
        role="tabpanel"
        v-if="showTab === 'content'"
      >
        <omegaup-course-assignment-list
          v-bind:content="assignments"
          v-bind:course-alias="data.course.alias"
          v-bind:assignment-form-mode="assignmentFormMode"
          v-on:emit-new="onNewAssignment"
          v-on:emit-edit="(assignment) => onEditAssignment(assignment)"
          v-on:emit-add-problems="(assignment) => onAddProblems(assignment)"
          v-on:emit-delete="
            (assignment) => $emit('delete-assignment', assignment)
          "
          v-on:emit-sort-content="
            (courseAlias, contentAliases) =>
              $emit('sort-content', courseAlias, contentAliases)
          "
        ></omegaup-course-assignment-list>
        <omegaup-course-assignment-details
          ref="assignment-details"
          v-bind:unlimited-duration-course="!data.course.finish_time"
          v-bind:finish-time-course="data.course.finish_time"
          v-bind:start-time-course="data.course.start_time"
          v-bind:assignment="assignment"
          v-bind:assignment-problems="assignmentProblems"
          v-bind:tagged-problems="data.taggedProblems"
          v-bind:invalid-parameter-name="invalidParameterName"
          v-on:add-problem="
            (assignment, problem) => $emit('add-problem', assignment, problem)
          "
          v-bind:assignment-form-mode.sync="assignmentFormMode"
          v-on:emit-add-problem="
            (assignment, problemAlias) =>
              $emit('add-problem', assignment, problemAlias)
          "
          v-on:emit-select-assignment="
            (assignment) => $emit('select-assignment', assignment)
          "
          v-on:remove-problem="
            (assignment, problem) =>
              $emit('remove-problem', assignment, problem)
          "
          v-on:sort-problems="
            (assignmentAlias, problemsAlias) =>
              $emit('sort-problems', assignmentAlias, problemsAlias)
          "
          v-on:cancel="onResetAssignmentForm"
          v-on:submit="
            (assignmentFormComponent, problems) =>
              $emit('submit-new-assignment', assignmentFormComponent, problems)
          "
          v-on:get-versions="
            (newProblemAlias, addProblemComponent) =>
              $emit('get-versions', newProblemAlias, addProblemComponent)
          "
        >
          <template slot="page-header"><span></span></template>
          <template slot="cancel-button">
            <button
              class="btn btn-secondary"
              type="reset"
              v-on:click.prevent="onResetAssignmentForm"
            >
              {{ T.wordsCancel }}
            </button></template
          ></omegaup-course-assignment-details
        >
      </div>

      <div
        class="tab-pane active"
        role="tabpanel"
        v-if="showTab === 'admission-mode'"
      >
        <omegaup-course-admision-mode
          v-bind:initial-admission-mode="data.course.admission_mode"
          v-bind:should-show-public-option="data.course.is_curator"
          v-bind:course-alias="data.course.alias"
          v-on:emit-update-admission-mode="
            (admisionMode) => $emit('update-admission-mode', admisionMode)
          "
        ></omegaup-course-admision-mode>
      </div>

      <div
        data-students-tab
        class="tab-pane active"
        role="tabpanel"
        v-if="showTab === 'students'"
      >
        <omegaup-course-add-students
          v-bind:students="data.students"
          v-bind:course-alias="data.course.alias"
          v-bind:identity-requests="data.identityRequests"
          v-on:emit-add-student="
            (participants) => $emit('add-student', participants)
          "
          v-on:emit-remove-student="
            (student) => $emit('remove-student', student)
          "
          v-on:emit-accept-request="
            (username) => $emit('accept-request', username)
          "
          v-on:emit-deny-request="(username) => $emit('deny-request', username)"
        ></omegaup-course-add-students>
      </div>

      <div
        class="tab-pane active pane-admins d-flex row"
        role="tabpanel"
        v-if="showTab === 'admins'"
      >
        <div class="col-md-6">
          <omegaup-common-admins
            v-bind:initial-admins="data.admins"
            v-bind:has-parent-component="true"
            v-on:emit-add-admin="
              (addAdminComponent) =>
                $emit('add-admin', addAdminComponent.username)
            "
            v-on:emit-remove-admin="
              (addAdminComponent) =>
                $emit('remove-admin', addAdminComponent.selected.username)
            "
          ></omegaup-common-admins>
        </div>
        <div class="col-md-6">
          <omegaup-common-groupadmins
            v-bind:initial-groups="data.groupsAdmins"
            v-bind:has-parent-component="true"
            v-on:emit-add-group-admin="
              (groupAdminsComponent) =>
                $emit('add-group-admin', groupAdminsComponent.groupAlias)
            "
            v-on:emit-remove-group-admin="
              (groupAdminsComponent) =>
                $emit('remove-group-admin', groupAdminsComponent.groupAlias)
            "
          ></omegaup-common-groupadmins>
        </div>
      </div>

      <div class="tab-pane active" role="tabpanel" v-if="showTab === 'clone'">
        <div class="card">
          <div class="card-body">
            <omegaup-course-clone
              v-bind:initial-alias="data.course.alias"
              v-bind:initial-name="data.course.name"
              v-on:clone="
                (alias, name, startTime) =>
                  $emit('clone', alias, name, startTime)
              "
            ></omegaup-course-clone>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch, Ref } from 'vue-property-decorator';
import course_Form from './Form.vue';
import course_AssignmentList from './AssignmentList.vue';
import course_AssignmentDetails from './AssignmentDetails.vue';
import course_AdmissionMode from './AdmissionMode.vue';
import course_AddStudents from './AddStudents.vue';
import common_Admins from '../common/Admins.vue';
import common_GroupAdmins from '../common/GroupAdmins.vue';
import course_Clone from './Clone.vue';
import T from '../../lang';
import { types } from '../../api_types';
import { omegaup } from '../../omegaup';

const now = new Date();
const finishTime = new Date();
finishTime.setHours(finishTime.getHours() + 5);
const defaultStartTime = now;
const defaultFinishTime = finishTime;
const availableTabs = [
  'course',
  'content',
  'problems',
  'admission-mode',
  'students',
  'admins',
  'clone',
];
const emptyAssignment: types.CourseAssignment = {
  problemset_id: 0,
  alias: '',
  description: '',
  name: '',
  has_runs: false,
  max_points: 0,
  start_time: defaultStartTime,
  finish_time: defaultFinishTime,
  order: 1,
  scoreboard_url: '',
  scoreboard_url_admin: '',
  assignment_type: 'homework',
};

@Component({
  components: {
    'omegaup-course-form': course_Form,
    'omegaup-course-assignment-list': course_AssignmentList,
    'omegaup-course-assignment-details': course_AssignmentDetails,
    'omegaup-course-admision-mode': course_AdmissionMode,
    'omegaup-course-add-students': course_AddStudents,
    'omegaup-common-admins': common_Admins,
    'omegaup-common-groupadmins': common_GroupAdmins,
    'omegaup-course-clone': course_Clone,
  },
})
export default class CourseEdit extends Vue {
  @Ref('assignment-details') readonly assignmentDetails!: Vue;
  @Prop() data!: types.CourseEditPayload;
  @Prop() invalidParameterName!: string;
  @Prop() initialTab!: string;

  T = T;
  showTab = this.initialTab;

  assignmentProblems = this.data.assignmentProblems;
  assignments = this.data.course.assignments;
  assignmentFormMode: omegaup.AssignmentFormMode =
    omegaup.AssignmentFormMode.Default;
  assignment = emptyAssignment;

  get courseURL(): string {
    return `/course/${this.data.course.alias}/`;
  }

  onNewAssignment(): void {
    this.assignmentFormMode = omegaup.AssignmentFormMode.New;
    this.assignment = emptyAssignment;
    this.assignmentProblems = [];
    this.$nextTick(() => {
      this.assignmentDetails.$el.scrollIntoView();
      (<HTMLElement>this.assignmentDetails.$refs.name).focus();
    });
  }

  onEditAssignment(assignment: types.CourseAssignment): void {
    this.assignmentFormMode = omegaup.AssignmentFormMode.Edit;
    this.assignment = assignment;
    this.$emit('select-assignment', this.assignment);
    this.$nextTick(() => {
      this.assignmentDetails.$el.scrollIntoView();
      (<HTMLElement>this.assignmentDetails.$refs.name).focus();
    });
  }

  onAddProblems(assignment: types.CourseAssignment): void {
    this.assignmentFormMode = omegaup.AssignmentFormMode.Edit;
    this.assignment = assignment;
    this.$emit('select-assignment', assignment);
    this.$nextTick(() => {
      this.assignmentDetails.$el.scrollIntoView();
    });
  }

  onCancel(): void {
    this.$emit('cancel', this.courseURL);
  }

  onResetAssignmentForm(): void {
    this.assignmentFormMode = omegaup.AssignmentFormMode.Default;
    window.scrollTo(0, 0);
  }

  onSelectAssignmentTab(): void {
    this.showTab = 'content';
    this.onResetAssignmentForm();
  }

  @Watch('initialTab')
  onInitialTabChanged(newValue: string): void {
    if (!availableTabs.includes(this.initialTab)) {
      this.showTab = 'course';
      return;
    }
    this.showTab = newValue;
  }
}
</script>
