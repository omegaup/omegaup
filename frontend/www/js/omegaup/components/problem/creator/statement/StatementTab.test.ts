import { createLocalVue, shallowMount } from '@vue/test-utils';

import store from '@/js/omegaup/problem/creator/store';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import T from '../../../../lang';
import * as ui from '../../../../ui';
import StatementTab from './StatementTab.vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('StatementTab.vue', () => {
  it('Should contain markdown buttons and contents and update the store accordingly', async () => {
    const wrapper = shallowMount(StatementTab, {
      localVue,
      store,
    });

    const markdownButtons = wrapper.find('div.wmd-button-bar');
    expect(markdownButtons.exists()).toBe(true);

    const textArea = wrapper.find('textarea.wmd-input');
    expect(textArea.exists()).toBe(true);
    textArea.setValue('Hello omegaUp');

    expect(wrapper.vm.currentMarkdown).toBe('Hello omegaUp');

    const markdownContent = wrapper.find('omegaup-markdown-stub');
    expect(markdownContent.exists()).toBe(true);

    await wrapper.trigger('click');

    expect(markdownContent.props()['markdown']).toBe(
      T.problemCreatorMarkdownPreviewInitialRender + 'Hello omegaUp',
    );

    expect(wrapper.vm.$store.state.problemMarkdown).toBe('');

    const markdownSaveButton = wrapper.find('button.btn-primary');
    expect(markdownSaveButton.exists()).toBe(true);
    await markdownSaveButton.trigger('click');

    expect(wrapper.vm.$store.state.problemMarkdown).toBe('Hello omegaUp');
  });

  describe('Image size validation', () => {
    it('Should allow pasting images under 256 KB', async () => {
      const wrapper = shallowMount(StatementTab, {
        localVue,
        store,
      });

      const textArea = wrapper.find('textarea.wmd-input');
      const smallFile = new File(['x'.repeat(100 * 1024)], 'small.png', {
        type: 'image/png',
      });

      const pasteEvent = {
        clipboardData: {
          items: [
            {
              type: 'image/png',
              getAsFile: () => smallFile,
            },
          ],
        },
        preventDefault: jest.fn(),
      };

      await textArea.trigger('paste', pasteEvent);

      expect(pasteEvent.preventDefault).not.toHaveBeenCalled();
    });

    it('Should reject pasting images over 256 KB and show error', async () => {
      const wrapper = shallowMount(StatementTab, {
        localVue,
        store,
      });

      const errorSpy = jest.spyOn(ui, 'error').mockImplementation(() => {});

      const textArea = wrapper.find('textarea.wmd-input');
      // Create a file larger than 256 KB
      const largeFile = new File(['x'.repeat(300 * 1024)], 'large.png', {
        type: 'image/png',
      });

      const pasteEvent = {
        clipboardData: {
          items: [
            {
              type: 'image/png',
              getAsFile: () => largeFile,
            },
          ],
        },
        preventDefault: jest.fn(),
      };

      await textArea.trigger('paste', pasteEvent);

      expect(pasteEvent.preventDefault).toHaveBeenCalled();
      expect(errorSpy).toHaveBeenCalled();

      errorSpy.mockRestore();
    });

    it('Should reject dropping images over 256 KB and show error', async () => {
      const wrapper = shallowMount(StatementTab, {
        localVue,
        store,
      });

      const errorSpy = jest.spyOn(ui, 'error').mockImplementation(() => {});

      const textArea = wrapper.find('textarea.wmd-input');
      // Create a file larger than 256 KB
      const largeFile = new File(['x'.repeat(300 * 1024)], 'large.png', {
        type: 'image/png',
      });

      const dropEvent = {
        dataTransfer: {
          files: [largeFile],
        },
        preventDefault: jest.fn(),
      };

      await textArea.trigger('drop', dropEvent);

      expect(dropEvent.preventDefault).toHaveBeenCalled();
      expect(errorSpy).toHaveBeenCalled();

      errorSpy.mockRestore();
    });

    it('Should allow non-image files without size validation', async () => {
      const wrapper = shallowMount(StatementTab, {
        localVue,
        store,
      });

      const textArea = wrapper.find('textarea.wmd-input');
      const textFile = new File(['x'.repeat(500 * 1024)], 'large.txt', {
        type: 'text/plain',
      });

      const pasteEvent = {
        clipboardData: {
          items: [
            {
              type: 'text/plain',
              getAsFile: () => textFile,
            },
          ],
        },
        preventDefault: jest.fn(),
      };

      await textArea.trigger('paste', pasteEvent);

      expect(pasteEvent.preventDefault).not.toHaveBeenCalled();
    });
  });
});
