<template>
  <div class="omegaup-course-addstudent card">
    <div class="card-body">
      <form
        class="form"
        @submit.prevent="
          $emit('emit-add-student', { participant, participants });
          participants = '';
        "
      >
        <div class="form-group">
          <p class="card-title">{{ T.courseEditAddStudentsDescription }}</p>
          <div class="d-flex align-items-center">
            <omegaup-common-typeahead
              class="w-100"
              :existing-options="searchResultUsers"
              :value.sync="participant"
              :max-results="10"
              @update-existing-options="
                (query) => $emit('update-search-result-users', query)
              "
            ></omegaup-common-typeahead>
            <button
              class="btn btn-secondary add-participant ml-2"
              :disabled="!participant"
              @click.prevent="addParticipantToList"
            >
              {{ T.courseEditAddStudentsAdd }}
            </button>
          </div>
        </div>
        <div class="form-group">
          <label>{{ T.wordsMultipleUser }}</label>
          <textarea
            v-model="participants"
            data-course-multiple-students-add
            class="form-control participants"
            rows="4"
          ></textarea>
        </div>
        <div class="form-group float-right">
          <button
            class="btn btn-primary user-add-bulk"
            :disabled="participants === ''"
            type="submit"
          >
            {{ T.wordsAddStudents }}
          </button>
        </div>
      </form>

      <!-- TODO(#9875): Replace with the dedicated empty-state component once that PR is merged -->
      <omegaup-view-unavailable
        v-if="students.length == 0"
        class="course-students-empty"
        icon="user-plus"
        :title="T.courseStudentsEmptyTitle"
        :description="T.courseStudentsEmptyDescription"
      >
        <button
          class="btn btn-primary mt-2"
          type="button"
          @click="focusParticipantInput"
        >
          {{ T.courseEditAddStudentsAdd }}
        </button>
      </omegaup-view-unavailable>
      <table v-else class="table table-striped table-over">
        <thead>
          <tr>
            <th>{{ T.courseEditAddStudentsStudent }}</th>
            <th class="align-right">
              {{ T.contestEditRegisteredAdminDelete }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students" :key="student.username">
            <td data-uploaded-students>
              <a :href="studentProgressUrl(student)">{{
                student.name || student.username
              }}</a>
            </td>
            <td>
              <button
                class="close"
                type="button"
                @click="$emit('emit-remove-student', student)"
              >
                ×
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="students.length > 0" class="float-right">
        <a class="btn btn-primary" :href="studentsProgressUrl()">
          {{ T.courseStudentsProgress }}
        </a>
      </div>
    </div>
    <omegaup-common-requests
      :data="identityRequests"
      :text-add-participant="T.wordsAddStudent"
      @accept-request="(request) => $emit('accept-request', request)"
      @deny-request="(request) => $emit('deny-request', request)"
    ></omegaup-common-requests>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import common_Typeahead from '../common/Typeahead.vue';
import common_Requests from '../common/Requests.vue';
import common_ViewUnavailable from '../common/ViewUnavailable.vue';

@Component({
  components: {
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-common-requests': common_Requests,
    'omegaup-view-unavailable': common_ViewUnavailable,
  },
})
export default class CourseAddStudents extends Vue {
  @Prop() courseAlias!: string;
  @Prop() students!: types.CourseStudent[];
  @Prop({ required: false }) identityRequests!: types.IdentityRequest[];
  @Prop() searchResultUsers!: types.ListItem[];

  T = T;
  studentUsername = '';
  participant: null | types.ListItem = null;
  participants = '';
  requests: types.IdentityRequest[] = [];

  studentProgressUrl(student: types.CourseStudent): string {
    return `/course/${this.courseAlias}/student/${student.username}/`;
  }

  studentsProgressUrl(): string {
    return `/course/${this.courseAlias}/students/`;
  }

  addParticipantToList(): void {
    if (this.participants.length) {
      this.participants += '\n';
    }
    this.participants += this.participant?.key;

    this.participant = null;
  }
  focusParticipantInput(): void {
    const input = this.$el.querySelector<HTMLInputElement>('input');
    input?.focus();
  }

  @Watch('identityRequests')
  onDataChange(): void {
    this.requests = this.identityRequests;
  }
}
</script>

<style>
.omegaup-course-addstudent th.align-right {
  text-align: right;
}

.course-students-empty {
  min-height: auto !important;
}
</style>
