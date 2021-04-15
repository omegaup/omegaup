<template>
  <div class="omegaup-course-addstudent card">
    <div class="card-body">
      <form
        class="form"
        @submit.prevent="
          $emit('emit-add-student', { participant, participants })
        "
      >
        <div class="form-group">
          <p class="card-title">{{ T.courseEditAddStudentsDescription }}</p>
          <label>{{ T.username }}</label>
          <span
            aria-hidden="true"
            class="glyphicon glyphicon-info-sign"
            data-placement="top"
            data-toggle="tooltip"
            :title="T.courseEditAddStudentsTooltip"
          ></span>
          <omegaup-autocomplete
            v-model="participant"
            :init="(el) => typeahead.userTypeahead(el)"
          ></omegaup-autocomplete>
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary" type="submit">
            {{ T.wordsAddStudents }}
          </button>
        </div>
        <div class="form-group">
          <label>{{ T.wordsMultipleUser }}</label>
          <textarea
            v-model="participants"
            class="form-control pariticipants"
            rows="4"
          ></textarea>
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary user-add-bulk" type="submit">
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
import * as typeahead from '../../typeahead';
import Autocomplete from '../Autocomplete.vue';
import common_Requests from '../common/Requests.vue';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
    'omegaup-common-requests': common_Requests,
  },
})
export default class CourseAddStudents extends Vue {
  @Prop() courseAlias!: string;
  @Prop() students!: types.CourseStudent[];
  @Prop({ required: false }) identityRequests!: types.IdentityRequest[];

  T = T;
  typeahead = typeahead;
  studentUsername = '';
  participant = '';
  participants = '';
  requests: types.IdentityRequest[] = [];

  studentProgressUrl(student: types.CourseStudent): string {
    return `/course/${this.courseAlias}/student/${student.username}/`;
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
