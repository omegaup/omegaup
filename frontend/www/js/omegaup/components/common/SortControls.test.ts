import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import * as ui from '../../ui';

import common_SortControls from './SortControls.vue';

describe('SortControls.vue', () => {
  it('Should handle the correct sort control', async () => {
    const wrapper = shallowMount(common_SortControls, {
      propsData: {
        column: 'title',
        columnType: 'string',
        initialModel: 'desc',
        initialOrderBy: 'ratio',
      },
    });

    expect(wrapper.vm.selected).toBe(false);
    expect(wrapper.vm.toggleSort).toBe('asc');
    expect(wrapper.vm.iconDisplayed).toBe('sort-alpha-up');

    wrapper.setProps({ initialOrderBy: 'title' });

    expect(wrapper.vm.selected).toBe(false);
    expect(wrapper.vm.iconDisplayed).toBe('sort-alpha-up');
  });
});
