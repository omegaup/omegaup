import { mount } from '@vue/test-utils';

import T from '../../lang';
import type { types } from '../../api_types';

import course_Intro from './Intro.vue';

const courseDetails: types.CourseDetails = {
  admission_mode: 'registration',
  alias: 'test-course',
  archived: false,
  assignments: [],
  clarifications: [],
  objective: 'Objetivo de prueba',
  level: '',
  needs_basic_information: true,
  description: 'Test',
  finish_time: new Date(),
  is_curator: true,
  is_admin: true,
  is_teaching_assistant: false,
  name: 'Course name',
  recommended: false,
  requests_user_information: 'yes',
  school_name: '',
  show_scoreboard: false,
  start_time: new Date(),
  student_count: 1,
  unlimited_duration: false,
  teaching_assistant_enabled: false,
};

const statements: {
  [name: string]: {
    gitObjectId?: string;
    markdown?: string;
    statementType?: string;
  };
} = {
  acceptTeacher: {
    markdown:
      '¿Deseas agregar a los organizadores del curso como tus profesores/profesoras?\n\nSi los aceptas, podrán ver la lista de los problemas que has resuelto y de los que has intentado resolver',
    statementType: 'accept_teacher',
    gitObjectId: '5a57d2644d12beea2d39601b1fc6d6f2344f4608',
  },
  privacy: {
    markdown:
      '# Consentimiento de privacidad de curso\n\nEl creador del curso ha solicitado acceder a tu información de usuario para poder contactarte, pero tu decides si deseas autorizar esta acción.\n\n¿Deseas compartir tus datos?\n',
    statementType: 'course_optional_consent',
    gitObjectId: '9a5d59f71386c5132d366977eb58c1956e86b8bc',
  },
};

describe('Intro.vue', () => {
  it('Should show the intro for a student in course', () => {
    const wrapper = mount(course_Intro, {
      propsData: {
        course: courseDetails,
        needsBasicInformation: true,
        shouldShowAcceptTeacher: true,
        statements,
      },
    });
    expect(wrapper.text()).toContain(courseDetails.name);
    expect(wrapper.text()).toContain(courseDetails.description);
    expect(wrapper.text()).toContain(T.courseIntroWhatYouWillLearn);
    expect(wrapper.text()).toContain(courseDetails.objective);

    expect(wrapper.text()).not.toContain(T.courseIntroImpartedBy);
  });
});
