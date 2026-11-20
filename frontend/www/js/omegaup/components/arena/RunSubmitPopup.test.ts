import { shallowMount } from '@vue/test-utils';

import arena_RunSubmitPopup from './RunSubmitPopup.vue';
import { sourceTemplates } from '../../grader/GraderTemplates';

describe('RunSubmitPopup.vue', () => {
  beforeEach(() => {
    const div = document.createElement('div');
    div.id = 'root';
    document.body.appendChild(div);
    localStorage.clear();
  });

  afterEach(() => {
    const rootDiv = document.getElementById('root');
    if (rootDiv) {
      document.body.removeChild(rootDiv);
    }
    const headerPayload = document.getElementById('header-payload');
    if (headerPayload) {
      document.body.removeChild(headerPayload);
    }
    localStorage.clear();
  });

  const nextSubmissionTimestamp = new Date(0);

  it('Should restore a saved language from storage when it is allowed for the current problem', async () => {
    localStorage.setItem('arena:selectedLanguage', 'py3');

    const wrapper = shallowMount(arena_RunSubmitPopup, {
      attachTo: '#root',
      propsData: {
        languages: ['py3', 'cpp17-gcc'],
        nextSubmissionTimestamp,
        preferredLanguage: 'cpp17-gcc',
      },
    });

    await wrapper.vm.$nextTick();

    const vm = wrapper.vm as any;
    expect(vm.selectedLanguage).toBe('py3');

    wrapper.destroy();
  });

  it('Should fall back to preferredLanguage when the saved language is not allowed', async () => {
    localStorage.setItem('arena:selectedLanguage', 'java');

    const wrapper = shallowMount(arena_RunSubmitPopup, {
      attachTo: '#root',
      propsData: {
        languages: ['cpp17-gcc'],
        nextSubmissionTimestamp,
        preferredLanguage: 'cpp17-gcc',
      },
    });

    await wrapper.vm.$nextTick();

    const vm = wrapper.vm as any;
    expect(vm.selectedLanguage).toBe('cpp17-gcc');

    wrapper.destroy();
  });

  it('Should revalidate the restored language after navigating to another problem', async () => {
    localStorage.setItem('arena:selectedLanguage', 'java');

    const wrapper = shallowMount(arena_RunSubmitPopup, {
      attachTo: '#root',
      propsData: {
        languages: ['java'],
        nextSubmissionTimestamp,
        preferredLanguage: 'java',
      },
    });

    await wrapper.vm.$nextTick();

    const vm = wrapper.vm as any;

    expect(vm.selectedLanguage).toBe('java');

    await wrapper.setProps({
      languages: ['cpp17-gcc'],
      preferredLanguage: 'cpp17-gcc',
    });

    await wrapper.vm.$nextTick();

    expect(vm.selectedLanguage).toBe('cpp17-gcc');

    wrapper.destroy();
  });

  it('Should emit submit-run with the current valid language after navigation', async () => {
    localStorage.setItem('arena:selectedLanguage', 'java');

    const wrapper = shallowMount(arena_RunSubmitPopup, {
      attachTo: '#root',
      propsData: {
        languages: ['java'],
        nextSubmissionTimestamp,
        preferredLanguage: 'java',
      },
    });

    await wrapper.vm.$nextTick();

    await wrapper.setProps({
      languages: ['cpp17-gcc'],
      preferredLanguage: 'cpp17-gcc',
    });

    await wrapper.vm.$nextTick();

    await wrapper.setData({
      code: 'int main() {}',
    });

    await wrapper.find('form[data-run-submit]').trigger('submit');

    const submitRunEvents = wrapper.emitted('submit-run');

    expect(submitRunEvents).toBeDefined();
    expect(submitRunEvents?.[0][1]).toBe('cpp17-gcc');

    wrapper.destroy();
  });

  it('Should load the system boilerplate when the user has no custom template', async () => {
    const wrapper = shallowMount(arena_RunSubmitPopup, {
      attachTo: '#root',
      propsData: {
        languages: ['py3'],
        nextSubmissionTimestamp,
        preferredLanguage: 'py3',
      },
    });

    await wrapper.vm.$nextTick();

    const vm = wrapper.vm as any;
    expect(vm.code).toBe(sourceTemplates.py);

    wrapper.destroy();
  });

  it('Should load the custom template for the selected language', async () => {
    localStorage.setItem('codeTemplates:py', 'print("mine")');

    const wrapper = shallowMount(arena_RunSubmitPopup, {
      attachTo: '#root',
      propsData: {
        languages: ['py3'],
        nextSubmissionTimestamp,
        preferredLanguage: 'py3',
      },
    });

    await wrapper.vm.$nextTick();

    const vm = wrapper.vm as any;
    expect(vm.code).toBe('print("mine")');

    wrapper.destroy();
  });

  it('Should load the custom template namespaced by the current user', async () => {
    const headerPayload = document.createElement('script');
    headerPayload.id = 'header-payload';
    headerPayload.setAttribute('type', 'text/json');
    headerPayload.textContent = JSON.stringify({ currentUsername: 'omegaup' });
    document.body.appendChild(headerPayload);
    localStorage.setItem(
      'codeTemplates:omegaup:cpp',
      'int main() { return 0; }',
    );

    const wrapper = shallowMount(arena_RunSubmitPopup, {
      attachTo: '#root',
      propsData: {
        languages: ['cpp17-gcc'],
        nextSubmissionTimestamp,
        preferredLanguage: 'cpp17-gcc',
      },
    });

    await wrapper.vm.$nextTick();

    const vm = wrapper.vm as any;
    expect(vm.code).toBe('int main() { return 0; }');

    wrapper.destroy();
  });

  it('Should switch between custom template and boilerplate when changing languages', async () => {
    localStorage.setItem('codeTemplates:py', 'print("mine")');

    const wrapper = shallowMount(arena_RunSubmitPopup, {
      attachTo: '#root',
      propsData: {
        languages: ['py3', 'cpp17-gcc'],
        nextSubmissionTimestamp,
        preferredLanguage: 'py3',
      },
    });

    await wrapper.vm.$nextTick();

    const vm = wrapper.vm as any;
    expect(vm.code).toBe('print("mine")');

    await wrapper
      .find('select[name="language"]')
      .find('option[value="cpp17-gcc"]')
      .setSelected();

    await wrapper.vm.$nextTick();

    expect(vm.code).toBe(sourceTemplates.cpp);

    wrapper.destroy();
  });

  it('Should persist the newly selected language to storage when the user changes it', async () => {
    const wrapper = shallowMount(arena_RunSubmitPopup, {
      attachTo: '#root',
      propsData: {
        languages: ['py3', 'cpp17-gcc'],
        nextSubmissionTimestamp,
        preferredLanguage: 'py3',
      },
    });

    await wrapper.vm.$nextTick();

    await wrapper
      .find('select[name="language"]')
      .find('option[value="cpp17-gcc"]')
      .setSelected();

    await wrapper.vm.$nextTick();

    expect(localStorage.getItem('arena:selectedLanguage')).toBe('cpp17-gcc');

    wrapper.destroy();
  });
});
