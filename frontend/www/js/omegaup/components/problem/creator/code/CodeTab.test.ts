import { createLocalVue, shallowMount } from '@vue/test-utils';

import CodeTab from './CodeTab.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('CodeTab.vue', () => {
  it('Should change code and extension in both wrapper and store after the file is uploaded', async () => {
    const wrapper = shallowMount(CodeTab, {
      localVue,
      store,
    });

    const testFile = new File(['print("Hello World")'], 'testfile.py', {
      type: 'text/x-python',
    });

    const mockReadFileMethod = jest
      .spyOn(wrapper.vm, 'readFile')
      .mockImplementation(() => testFile);

    const fileInput = wrapper.find('input[type=file]');
    await fileInput.trigger('change');

    expect(mockReadFileMethod).toHaveBeenCalled();

    await new Promise((r) => setTimeout(r, 1000)); // Waiting for the FileReader to complete reading the file.

    expect(wrapper.vm.code).toBe('print("Hello World")');
    expect(wrapper.vm.extension).toBe('py');

    const codeSaveButton = wrapper.find('button');
    expect(codeSaveButton.exists()).toBe(true);
    await codeSaveButton.trigger('click');

    expect(wrapper.vm.$store.state.problemCodeContent).toBe(
      'print("Hello World")',
    );
    expect(wrapper.vm.$store.state.problemCodeExtension).toBe('py');
  });
});
