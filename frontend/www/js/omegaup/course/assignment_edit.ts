import { omegaup, OmegaUp } from '../omegaup';
import { messages, types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import course_AssignmentDetails from '../components/course/AssignmentDetails.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseAssignmentEditPayload();
  const courseAlias = payload.course.alias;
  const courseEdit = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-course-edit', {
        props: {
          assignmentFormMode: omegaup.AssignmentFormMode.Edit,
          unlimitedDurationCourse: !payload.course.finish_time,
          startTimeCourse: payload.course.start_time,
          assignment: payload.assignment,
          invalidParameterName: this.invalidParameterName,
          shouldAddProblems: false,
        },
        on: {
          submit: (source: course_AssignmentDetails) => {
            const params = {
              assignment: source.alias,
              course: courseAlias,
              name: source.name,
              description: source.description,
              start_time: source.startTime.getTime() / 1000,
              assignment_type: source.assignmentType,
            };

            if (source.unlimitedDuration) {
              Object.assign(params, { unlimited_duration: true });
            } else {
              Object.assign(params, {
                finish_time: source.finishTime.getTime() / 1000,
              });
            }

            api.Course.updateAssignment(params)
              .then(() => {
                ui.success(T.courseAssignmentUpdated);
                this.invalidParameterName = '';
              })
              .catch((error) => {
                ui.apiError(error);
                this.invalidParameterName = error.parameter || '';
              });
          },
          cancel: () => {
            window.location.href = `/course/${courseAlias}/`;
          },
        },
      });
    },
    data: () => ({
      invalidParameterName: <string | null>null,
    }),
    components: {
      'omegaup-course-edit': course_AssignmentDetails,
    },
  });
});
