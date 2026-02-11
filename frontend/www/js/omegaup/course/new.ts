import course_Form from '../components/course/Form.vue';
import { OmegaUp } from '../omegaup';
import { messages, types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import T from '../lang';

OmegaUp.on('ready', () => {
  const now = new Date();
  const finishTime = new Date();
  finishTime.setDate(finishTime.getDate() + 30);
  const defaultStartTime = now;
  const defaultFinishTime = finishTime;
  const searchResultSchools: types.SchoolListItem[] = [];
  const payload = types.payloadParsers.CourseNewPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-form': course_Form,
    },
    data: () => ({
      invalidParameterName: null as null | string,
      searchResultSchools: searchResultSchools,
    }),
    render: function (createElement) {
      return createElement('omegaup-course-form', {
        props: {
          course: {
            alias: '',
            description: '',
            start_time: defaultStartTime,
            finish_time: defaultFinishTime,
            show_scoreboard: false,
            level: null,
            objective: null,
            name: '',
            school_name: '',
            languages: Object.keys(payload.languages),
            needs_basic_information: false,
            requests_user_information: 'no',
            is_curator: payload.is_curator,
            is_admin: payload.is_admin,
            hasVisitedSection: payload.hasVisitedSection,
          },
          allLanguages: payload.languages,
          invalidParameterName: this.invalidParameterName,
          searchResultSchools: this.searchResultSchools,
          hasVisitedSection: payload.hasVisitedSection,
        },
        on: {
          submit: (request: messages.CourseCreateRequest) => {
            new Promise<number | null>((resolve, reject) => {
              if (request.school?.key) {
                resolve(request.school.key);
              } else if (request.school?.value) {
                api.School.create({ name: request.school.value })
                  .then((data) => {
                    resolve(data.school_id);
                  })
                  .catch((error) => {
                    ui.apiError({ error: error.message });
                  });
              } else {
                reject(new Error(T.schoolNotSelected));
              }
            })
              .then((schoolId) => {
                if (schoolId) {
                  request.school_id = schoolId;
                }
                api.Course.create(request)
                  .then(() => {
                    this.invalidParameterName = '';
                    window.location.replace(
                      `/course/${request.alias}/edit/#content`,
                    );
                  })
                  .catch((error) => {
                    ui.apiError({ error: error.message });
                    this.invalidParameterName = error.parameter || '';
                  });
              })
              .catch((error) => {
                ui.apiError({ error: error.message });
              });
          },
          cancel: () => {
            window.location.href = '/course/';
          },
          'update-search-result-schools': (query: string) => {
            api.School.list({ query })
              .then(({ results }) => {
                if (!results.length) {
                  this.searchResultSchools = [
                    {
                      key: 0,
                      value: query,
                    },
                  ];
                  return;
                }
                this.searchResultSchools = results.map(
                  ({ key, value }: types.SchoolListItem) => ({
                    key,
                    value,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'invalid-languages': () => {
            ui.error(T.courseNewFormLanguagesRequired);
            this.invalidParameterName = 'languages';
          },
          'clear-language-error': () => {
            if (this.invalidParameterName === 'languages') {
              this.invalidParameterName = null;
              ui.dismissNotifications();
            }
          },
        },
      });
    },
  });
});
