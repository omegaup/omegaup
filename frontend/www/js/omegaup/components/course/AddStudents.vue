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
            class="form-control pariticipants"
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
      <div v-if="students.length == 0">
        <div class="empty-category">
          {{ T.courseStudentsEmpty }}
        </div>
      </div>
      <table v-else class="table table-striped table-over">
        <thead>
          <tr>
            <th>{{ T.wordsUser }}</th>
            <th class="align-right">
              {{ T.contestEditRegisteredAdminDelete }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students" :key="student.username">
            <td>
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
      <div class="float-right">
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

@Component({
  components: {
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-common-requests': common_Requests,
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
</style>
