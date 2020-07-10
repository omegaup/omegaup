import { mount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';

import course_Clone from './Clone.vue';

describe('Clone.vue', () => {
  it('Should handle clone course view', () => {
    const wrapper = mount(course_Clone, {
      propsData: {
        initialAlias: 'CA',
        initialName: 'initial name',
      },
    });

    expect(
      wrapper.find('.omegaup-course-clone input[type="date"]').text(),
    ).toBe('');
  });
});
