<template>
  <div>
    <div class="page-header">
      <h1>
        <span>{{ T.wordsEditCourse }} {{ data.course.name }}</span>
        <small>
          &ndash;
          <a v-bind:href="`/course/${data.course.alias}/`">
            {{ T.courseEditGoToCourse }}
          </a>
        </small>
      </h1>
    </div>
    <ul class="nav nav-pills">
      <li class="nav-item" role="presentation">
        <a
          href="#"
          class="nav-link"
          data-tab-course
          v-on:click="showTab = 'course'"
          v-bind:class="{ active: showTab === 'course' }"
          >{{ T.courseEdit }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#"
          class="nav-link"
          data-tab-assignments
          v-on:click="showTab = 'assignments'"
          v-bind:class="{ active: showTab === 'assignments' }"
          >{{ T.wordsAssignments }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#"
          class="nav-link"
          data-tab-problems
          v-on:click="showTab = 'problems'"
          v-bind:class="{ active: showTab === 'problems' }"
          >{{ T.wordsProblems }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#"
          class="nav-link"
          data-tab-admission-mode
          v-on:click="showTab = 'admission-mode'"
          v-bind:class="{ active: showTab === 'admission-mode' }"
          >{{ T.contestNewFormAdmissionMode }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#"
          class="nav-link"
          data-tab-students
          v-on:click="showTab = 'students'"
          v-bind:class="{ active: showTab === 'students' }"
          >{{ T.courseEditStudents }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#"
          class="nav-link"
          data-tab-admins
          v-on:click="showTab = 'admins'"
          v-bind:class="{ active: showTab === 'admins' }"
          >{{ T.courseEditAdmins }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#"
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
        ></omegaup-course-form>
      </div>

      <div
        class="tab-pane active"
        role="tabpanel"
        v-if="showTab === 'assignments'"
      >
        <omegaup-course-assignment-list
          v-bind:assignments="data.course.assignments"
          v-bind:course-alias="data.course.alias"
        ></omegaup-course-assignment-list>
        <omegaup-course-assignment-details
          v-if="data.selectedAssignment"
          v-bind:show="false"
          v-bind:update="true"
          v-bind:assignment="data.selectedAssignment"
        ></omegaup-course-assignment-details>
      </div>

      <div
        class="tab-pane active"
        role="tabpanel"
        v-if="showTab === 'problems'"
      >
        <omegaup-course-problem-list
          v-if="data.selectedAssignment"
          v-bind:assignments="data.course.assignments"
          v-bind:assignment-problems="data.assignmentProblems"
          v-bind:tagged-problems="data.taggedProblems"
          v-bind:selected-assignment="data.selectedAssignment"
        ></omegaup-course-problem-list>
      </div>

      <div
        class="tab-pane active"
        role="tabpanel"
        v-if="showTab === 'admission-mode'"
      >
        <omegaup-course-admision-mode
          v-bind:initial-admission-mode="data.course.admission_mode"
          v-bind:should-show-public-option="data.course.is_curator"
          v-bind:admission-mode-description="
            T.courseEditAdmissionModeDescription
          "
          v-bind:course-alias="data.course.alias"
        ></omegaup-course-admision-mode>
      </div>

      <div
        class="tab-pane active"
        role="tabpanel"
        v-if="showTab === 'students'"
      >
        <omegaup-course-add-students
          v-bind:students="data.students"
          v-bind:course-alias="data.course.alias"
          v-bind:identity-requests="data.identityRequests"
        ></omegaup-course-add-students>
      </div>

      <div class="tab-pane active" role="tabpanel" v-if="showTab === 'admins'">
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

      <div class="tab-pane active" role="tabpanel" v-if="showTab === 'clone'">
        <omegaup-course-clone
          v-bind:initial-alias="data.course.alias"
          v-bind:initial-name="data.course.name"
        ></omegaup-course-clone>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import course_Form from './Form.vue';
import course_AssignmentList from './AssignmentList.vue';
import course_AssignmentDetails from './AssignmentDetails.vue';
import course_ProblemList from './ProblemList.vue';
import course_AdmissionMode from './AdmissionMode.vue';
import course_AddStudents from './AddStudents.vue';
import common_Admins from '../common/Admins.vue';
import common_GroupAdmins from '../common/GroupAdmins.vue';
import course_Clone from './Clone.vue';
import T from '../../lang';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-course-form': course_Form,
    'omegaup-course-assignment-list': course_AssignmentList,
    'omegaup-course-assignment-details': course_AssignmentDetails,
    'omegaup-course-problem-list': course_ProblemList,
    'omegaup-course-admision-mode': course_AdmissionMode,
    'omegaup-course-add-students': course_AddStudents,
    'omegaup-common-admins': common_Admins,
    'omegaup-common-groupadmins': common_GroupAdmins,
    'omegaup-course-clone': course_Clone,
  },
})
export default class CourseEdit extends Vue {
  @Prop() data!: types.CourseEditPayload;

  T = T;
  showTab = 'course';
}
</script>
