<template>
  <div class="course-edit">
    <div class="page-header">
      <h1>
        {{ readOnly ? T.omegaupTitleCourseDetails : T.wordsEditCourse }}
        <span
          data-course-name
          :class="{ 'text-secondary': data.course.archived }"
          >{{ data.course.name }}</span
        >
        <small>
          &ndash;
          <a :href="courseURL">
            {{ T.courseEditGoToCourse }}
          </a>
        </small>
      </h1>
    </div>
    <ul v-if="!readOnly" class="nav nav-pills mt-4">
      <li class="nav-item" role="presentation">
        <a
          href="#course"
          class="nav-link"
          data-tab-course
          :class="{ active: showTab === 'course' }"
          @click="showTab = 'course'"
          >{{ T.courseEdit }}</a
        >
      </li>
      <li class="nav-item" role="presentation" data-course-edit-content>
        <a
          href="#content"
          class="nav-link"
          data-tab-content
          :class="{ active: showTab === 'content' }"
          @click="onSelectAssignmentTab"
          >{{ T.wordsContent }}</a
        >
      </li>
      <li class="nav-item" role="presentation" data-course-edit-admission-mode>
        <a
          href="#admission-mode"
          class="nav-link"
          data-tab-admission-mode
          :class="{ active: showTab === 'admission-mode' }"
          @click="showTab = 'admission-mode'"
          >{{ T.contestNewFormAdmissionMode }}</a
        >
      </li>
      <li class="nav-item" role="presentation" data-course-edit-students>
        <a
          href="#students"
          class="nav-link"
          data-tab-students
          :class="{ active: showTab === 'students' }"
          @click="showTab = 'students'"
          >{{ T.courseEditStudents }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#admins"
          class="nav-link"
          data-tab-admins
          :class="{ active: showTab === 'admins' }"
          @click="showTab = 'admins'"
          >{{ T.courseEditAdmins }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#clone"
          class="nav-link"
          data-tab-clone
          :class="{ active: showTab === 'clone' }"
          @click="showTab = 'clone'"
          >{{ T.courseEditClone }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#archive"
          class="nav-link"
          data-tab-archive
          :class="{ active: showTab === 'archive' }"
          @click="showTab = 'archive'"
          >{{ T.courseEditArchive }}</a
        >
      </li>
    </ul>

    <div class="tab-content mt-2">
      <div v-if="showTab === 'course'" class="tab-pane active" role="tabpanel">
        <omegaup-course-form
          :update="true"
          :course="data.course"
          :all-languages="data.allLanguages"
          :search-result-schools="searchResultSchools"
          :read-only="readOnly"
          :invalid-parameter-name="invalidParameterName"
          @emit-cancel="onCancel"
          @submit="(request) => $emit('submit-edit-course', request)"
          @update-search-result-schools="
            (query) => $emit('update-search-result-schools', query)
          "
          @invalid-languages="$emit('invalid-languages')"
          @clear-language-error="$emit('clear-language-error')"
        ></omegaup-course-form>
      </div>

      <div
        v-if="showTab === 'content'"
        data-content-tab
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-course-assignment-list
          :content="assignments"
          :course-alias="data.course.alias"
          :assignment-form-mode="assignmentFormMode"
          @emit-new="onNewAssignment"
          @emit-edit="(assignment) => onEditAssignment(assignment)"
          @emit-add-problems="(assignment) => onAddProblems(assignment)"
          @emit-delete="(assignment) => $emit('delete-assignment', assignment)"
          @emit-sort-content="
            (courseAlias, contentAliases) =>
              $emit('sort-content', courseAlias, contentAliases)
          "
        ></omegaup-course-assignment-list>
        <omegaup-course-assignment-details
          ref="assignment-details"
          :unlimited-duration-course="!data.course.finish_time"
          :finish-time-course="data.course.finish_time"
          :start-time-course="data.course.start_time"
          :assignment="assignment"
          :assignment-problems="assignmentProblems"
          :tagged-problems="data.taggedProblems"
          :invalid-parameter-name="invalidParameterName"
          :assignment-form-mode.sync="assignmentFormMode"
          :course-alias="data.course.alias"
          :search-result-problems="searchResultProblems"
          @update-search-result-problems="
            (query) => $emit('update-search-result-problems', query)
          "
          @add-problem="
            (assignment, problem) => $emit('add-problem', assignment, problem)
          "
          @emit-add-problem="
            (assignment, problemAlias) =>
              $emit('add-problem', assignment, problemAlias)
          "
          @emit-select-assignment="
            (assignment) => $emit('select-assignment', assignment)
          "
          @remove-problem="
            (assignment, problem) =>
              $emit('remove-problem', assignment, problem)
          "
          @sort-problems="
            (assignmentAlias, problemsAlias) =>
              $emit('sort-problems', assignmentAlias, problemsAlias)
          "
          @cancel="onResetAssignmentForm"
          @add-assignment="(params) => $emit('add-assignment', params)"
          @update-assignment="(params) => $emit('update-assignment', params)"
          @get-versions="(request) => $emit('get-versions', request)"
        >
          <template #page-header><span></span></template>
          <template #cancel-button>
            <button
              class="btn btn-secondary"
              type="reset"
              @click.prevent="onResetAssignmentForm"
            >
              {{ T.wordsCancel }}
            </button></template
          ></omegaup-course-assignment-details
        >
      </div>

      <div
        v-if="showTab === 'admission-mode'"
        data-admission-mode-tab
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-course-admision-mode
          :admission-mode="data.course.admission_mode"
          :should-show-public-option="data.course.is_curator"
          :course-alias="data.course.alias"
          :show-in-public-courses-list="data.course.recommended"
          @update-admission-mode="
            (request) => $emit('update-admission-mode', request)
          "
        ></omegaup-course-admision-mode>
      </div>

      <div
        v-if="showTab === 'students'"
        data-students-tab
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-course-add-students
          :students="data.students"
          :course-alias="data.course.alias"
          :identity-requests="data.identityRequests"
          :search-result-users="searchResultUsers"
          @emit-add-student="
            (participants) => $emit('add-student', participants)
          "
          @emit-remove-student="(student) => $emit('remove-student', student)"
          @accept-request="(request) => $emit('accept-request', request)"
          @deny-request="(request) => $emit('deny-request', request)"
          @update-search-result-users="
            (query) => $emit('update-search-result-users', query)
          "
        ></omegaup-course-add-students>
      </div>

      <div
        v-if="showTab === 'admins'"
        class="tab-pane active pane-admins d-flex row"
        role="tabpanel"
      >
        <div class="col-md-6">
          <omegaup-common-admins
            :admins="data.admins"
            :search-result-users="searchResultUsers"
            @add-admin="(username) => $emit('add-admin', username)"
            @remove-admin="(username) => $emit('remove-admin', username)"
            @update-search-result-users="
              (query) => $emit('update-search-result-users', query)
            "
          ></omegaup-common-admins>
        </div>
        <div class="col-md-6">
          <omegaup-common-groupadmins
            :group-admins="data.groupsAdmins"
            :search-result-groups="searchResultGroups"
            @add-group-admin="
              (groupAlias) => $emit('add-group-admin', groupAlias)
            "
            @remove-group-admin="
              (groupAlias) => $emit('remove-group-admin', groupAlias)
            "
            @update-search-result-groups="
              (query) => $emit('update-search-result-groups', query)
            "
          ></omegaup-common-groupadmins>
        </div>
        <div class="col-md-6">
          <omegaup-common-teaching-assistants
            :teaching-assistants="data.teachingAssistants"
            :search-result-users="searchResultUsers"
            @add-teaching-assistant="
              (username) => $emit('add-teaching-assistant', username)
            "
            @remove-teaching-assistant="
              (username) => $emit('remove-teaching-assistant', username)
            "
            @update-search-result-users="
              (query) => $emit('update-search-result-users', query)
            "
          ></omegaup-common-teaching-assistants>
        </div>
        <div class="col-md-6">
          <omegaup-common-group-teaching-assistants
            :group-teaching-assistants="data.groupsTeachingAssistants"
            :search-result-groups="searchResultGroups"
            @add-group-teaching-assistant="
              (groupAlias) => $emit('add-group-teaching-assistant', groupAlias)
            "
            @remove-group-teaching-assistant="
              (groupAlias) =>
                $emit('remove-group-teaching-assistant', groupAlias)
            "
            @update-search-result-groups="
              (query) => $emit('update-search-result-groups', query)
            "
          ></omegaup-common-group-teaching-assistants>
        </div>
      </div>

      <div v-if="showTab === 'clone'" class="tab-pane active" role="tabpanel">
        <div class="card">
          <div class="card-body">
            <omegaup-course-clone
              class="mb-4"
              :initial-alias="data.course.alias"
              :initial-name="data.course.name"
              @clone="
                (alias, name, startTime) =>
                  $emit('clone', alias, name, startTime)
              "
            ></omegaup-course-clone>
            <omegaup-course-generate-link-clone
              v-if="data.course.admission_mode !== admissionMode.Public"
              :alias="data.course.alias"
              :token="token"
              @generate-link="(alias) => $emit('generate-link', alias)"
            ></omegaup-course-generate-link-clone>
          </div>
        </div>
      </div>
      <div v-if="showTab === 'archive'" class="tab-pane active" role="tabpanel">
        <omegaup-common-archive
          :already-archived="alreadyArchived"
          :archive-button-description="
            alreadyArchived ? T.wordsUnarchiveCourse : T.wordsArchiveCourse
          "
          :archive-confirm-text="T.courseArchiveConfirmText"
          :archive-header-title="T.wordsArchiveCourse"
          :archive-help-text="T.courseArchiveHelpText"
          @archive="onArchiveCourse"
        ></omegaup-common-archive>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch, Ref } from 'vue-property-decorator';
import course_Form from './Form.vue';
import course_AssignmentList from './AssignmentList.vue';
import common_Archive from '../common/Archive.vue';
import course_AssignmentDetails from './AssignmentDetails.vue';
import course_AdmissionMode from './AdmissionMode.vue';
import course_AddStudents from './AddStudents.vue';
import common_Admins from '../common/Admins.vue';
import common_GroupAdmins from '../common/GroupAdmins.vue';
import common_TeachingAssistants from '../common/TeachingAssistants.vue';
import common_GroupTeachingAssistants from '../common/GroupTeachingAssistants.vue';
import course_Clone from './Clone.vue';
import course_GenerateLinkClone from './GenerateLinkClone.vue';
import T from '../../lang';
import type { types } from '../../api_types';
import { omegaup } from '../../omegaup';
import { AdmissionMode } from '../common/Publish.vue';

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
  opened: false,
  order: 1,
  scoreboard_url: '',
  scoreboard_url_admin: '',
  assignment_type: 'homework',
  problemCount: 0,
};

@Component({
  components: {
    'omegaup-course-form': course_Form,
    'omegaup-common-archive': common_Archive,
    'omegaup-course-assignment-list': course_AssignmentList,
    'omegaup-course-assignment-details': course_AssignmentDetails,
    'omegaup-course-admision-mode': course_AdmissionMode,
    'omegaup-course-add-students': course_AddStudents,
    'omegaup-common-admins': common_Admins,
    'omegaup-common-groupadmins': common_GroupAdmins,
    'omegaup-common-teaching-assistants': common_TeachingAssistants,
    'omegaup-common-group-teaching-assistants': common_GroupTeachingAssistants,
    'omegaup-course-clone': course_Clone,
    'omegaup-course-generate-link-clone': course_GenerateLinkClone,
  },
})
export default class CourseEdit extends Vue {
  @Ref('assignment-details') readonly assignmentDetails!: Vue;
  @Prop() data!: types.CourseEditPayload;
  @Prop() invalidParameterName!: string;
  @Prop() initialTab!: string;
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop() searchResultProblems!: types.ListItem[];
  @Prop() searchResultGroups!: types.ListItem[];
  @Prop() searchResultTeachingAssistants!: types.ListItem[];
  @Prop() searchResultGroupsTeachingAssistants!: types.ListItem[];
  @Prop() searchResultSchools!: types.SchoolListItem[];
  @Prop() readOnly!: boolean;

  T = T;
  showTab = this.initialTab;
  admissionMode = AdmissionMode.Private;
  alreadyArchived = this.data.course.archived;

  assignmentProblems = this.data.assignmentProblems;
  assignments = this.data.course.assignments;
  assignmentFormMode: omegaup.AssignmentFormMode =
    omegaup.AssignmentFormMode.Default;
  assignment = emptyAssignment;
  token = '';

  get courseURL(): string {
    return `/course/${this.data.course.alias}/`;
  }

  onNewAssignment(): void {
    this.assignmentFormMode = omegaup.AssignmentFormMode.New;
    this.assignment = emptyAssignment;
    this.assignmentProblems = [];
    this.$nextTick(() => {
      this.assignmentDetails.$el.scrollIntoView();
      (this.assignmentDetails.$refs.name as HTMLElement).focus();
    });
  }

  onEditAssignment(assignment: types.CourseAssignment): void {
    this.assignmentFormMode = omegaup.AssignmentFormMode.Edit;
    this.assignment = assignment;
    this.$emit('select-assignment', this.assignment);
    this.$nextTick(() => {
      this.assignmentDetails.$el.scrollIntoView();
      (this.assignmentDetails.$refs.name as HTMLElement).focus();
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

  onArchiveCourse(archive: boolean): void {
    this.$emit('archive-course', this.data.course.alias, archive);
    this.alreadyArchived = archive;
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
