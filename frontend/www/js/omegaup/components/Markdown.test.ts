import { mount } from '@vue/test-utils';

import * as ui from '../ui';

import omegaup_Markdown from './Markdown.vue';

jest.mock('../ui', () => {
  const actual = jest.requireActual('../ui');
  return {
    __esModule: true,
    ...actual,
    copyToClipboard: jest.fn(),
  };
});

describe('Markdown.vue', () => {
  beforeEach(() => {
    (ui.copyToClipboard as jest.Mock).mockClear();
  });

  it('Should render markdown contents', () => {
    const wrapper = mount(omegaup_Markdown, {
      propsData: {
        markdown: '_Hello_, **World**!',
      },
    });

    expect(wrapper.find('p').exists()).toBeTruthy();
    expect(wrapper.text()).toContain('Hello, World!');
  });

  it('Should inject copy button for inline code elements', async () => {
    const token = '7cbe8d3122700dab325c7dd20ee1a5ce415672f1';
    const template = 'Here is some code: <span><code>%(token)</code></span>';

    const markdown = ui.formatString(template, { token });

    const wrapper = mount(omegaup_Markdown, {
      propsData: {
        markdown,
      },
    });

    await wrapper.vm.$nextTick();

    const codeElement = wrapper.find('span > code');
    expect(codeElement.exists()).toBeTruthy();

    const copyButton = wrapper.find('button.copy-btn');
    expect(copyButton.exists()).toBeTruthy();

    const copySpy = jest
      .spyOn(ui, 'copyToClipboard')
      .mockImplementation(() => {});

    await copyButton.trigger('click');

    expect(copySpy).toHaveBeenCalledWith(token);
    copySpy.mockRestore();

    const copyHint = wrapper.find('.copy-hint');
    expect(copyHint.exists()).toBeTruthy();
  });
});
