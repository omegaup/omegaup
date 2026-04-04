import { shallowMount } from '@vue/test-utils';
import type { types } from '../../api_types';

import course_LibinteractiveGen from './Gen.vue';

describe('Gen.vue', () => {
  beforeAll(() => {
    const div = document.createElement('div');
    div.id = 'root';
    document.body.appendChild(div);
  });

  afterAll(() => {
    const rootDiv = document.getElementById('root');
    if (rootDiv) {
      document.removeChild(rootDiv);
    }
  });

  it('Should handle empty form to be filled', async () => {
    const wrapper = shallowMount(course_LibinteractiveGen, {
      attachTo: '#root',
      propsData: {
        language: null,
        os: null,
        name: null,
        idl: null,
      },
    });

    let language = wrapper.find('select[name="language"]')
      .element as HTMLInputElement;
    expect(language.value).toEqual('');
    let os = wrapper.find('select[name="os"]').element as HTMLInputElement;
    expect(os.value).toEqual('');
    let name = wrapper.find('input[name="name"]').element as HTMLInputElement;
    expect(name.value).toEqual('');
    let idl = wrapper.find('textarea[name="idl"]').element as HTMLInputElement;
    expect(idl.value).toEqual('');

    await wrapper.setData({
      currentLanguage: 'java',
      currentOs: 'unix',
      currentName: 'sums',
      currentIdl: 'any text',
    });

    await wrapper.find('b-button-stub[type="submit"]').trigger('click');

    language = wrapper.find('select[name="language"]')
      .element as HTMLInputElement;
    expect(language.value).toEqual('java');
    os = wrapper.find('select[name="os"]').element as HTMLInputElement;
    expect(os.value).toEqual('unix');
    name = wrapper.find('input[name="name"]').element as HTMLInputElement;
    expect(name.value).toEqual('sums');
    idl = wrapper.find('textarea[name="idl"]').element as HTMLInputElement;
    expect(idl.value).toEqual('any text');

    wrapper.destroy();
  });

  it('Should handle form with initial error', async () => {
    const error: types.LibinteractiveError = {
      description: 'some error',
      field: 'idl',
    };

    const wrapper = shallowMount(course_LibinteractiveGen, {
      propsData: {
        language: null,
        os: null,
        name: null,
        idl: null,
        error,
      },
    });

    expect(wrapper.find('textarea[name="idl"]').attributes('class')).toContain(
      'is-invalid',
    );
    expect(wrapper.find('pre>code').text()).toBe('some error');
  });
});
