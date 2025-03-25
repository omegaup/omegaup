import { mount } from '@vue/test-utils';

import type { types } from '../../api_types';

import problem_StatementEdit from './StatementEdit.vue';

describe('StatementEdit.vue', () => {
  it('Should handle unrecognized source filename error', () => {
    const wrapper = mount(problem_StatementEdit, {
      propsData: {
        statement: {
          markdown: `# test with embed code in statement
Here we can add code.
<details>
  <summary>
    Example:
  </summary>

  {{sample.cpp}}

  </details>`,
          sources: {},
        } as types.ProblemStatement,
        status: 'unlocked',
        availableTokens: 0,
        allTokens: 0,
      },
    });

    expect(wrapper.find('div[data-markdown-statement]').text()).toContain(
      'Unrecognized source filename: sample.cpp',
    );
  });

  it('Should handle a valid source filename with content', async () => {
    const wrapper = mount(problem_StatementEdit, {
      propsData: {
        statement: {
          markdown: `# test with embed code in statement
Here we can add code.
<details>
  <summary>
    Example:
  </summary>

  {{sample.cpp}}

  </details>`,
          sources: {
            'sample.cpp': `#include <iostream>
      
      int main() {
        std::cout << "This is only an example";
        return 0;
      }`,
          },
          images: {},
          language: 'en',
        } as types.ProblemStatement,
        status: 'unlocked',
        availableTokens: 0,
        allTokens: 0,
      },
    });

    expect(wrapper.find('details').attributes()).toMatchObject({});
    await wrapper.find('details > summary').trigger('click');
    expect(wrapper.find('details').attributes()).toMatchObject({ open: '' });
    expect(wrapper.find('div[data-markdown-statement]').text()).toContain(
      '#include <iostream>',
    );
    expect(wrapper.find('div[data-markdown-statement]').text()).toContain(
      'This is only an example',
    );
  });
});
