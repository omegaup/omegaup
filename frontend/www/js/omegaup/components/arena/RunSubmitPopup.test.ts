import { shallowMount } from '@vue/test-utils';

import arena_RunSubmitPopup from './RunSubmitPopup.vue';

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
