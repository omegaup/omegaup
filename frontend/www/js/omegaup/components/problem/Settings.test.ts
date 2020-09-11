import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import { types } from '../../api_types';
import T from '../../lang';

import problem_Settings from './Settings.vue';

const baseSettingsProps = {
  errors: [],
  extraWallTimeLimit: 0,
  initialLanguage:
    'c11-clang,c11-gcc,cpp11-clang,cpp11-gcc,cpp17-clang,cpp17-gcc,cs,hs,java,lua,pas,py2,py3,rb',
  initialValidator: 'token',
  inputLimit: 10240,
  memoryLimit: 32768,
  outputLimit: 10240,
  overallWallTimeLimit: 6000,
  timeLimit: 1000,
  validLanguages: {
    'c11-clang,c11-gcc,cpp11-clang,cpp11-gcc,cpp17-clang,cpp17-gcc,cs,hs,java,lua,pas,py2,py3,rb':
      'C, C++, C++11, C#, Haskell, Java, Pascal, Python, Ruby, Lua',
    'kj,kp': 'Karel',
    cat: T.wordsJustOutput,
    '': T.wordsNoSubmissions,
  },
  validatorTimeLimit: 0,
  validatorTypes: {
    'token-caseless': T.problemEditFormTokenCaseless,
    'token-numeric': T.problemEditFormNumericTokensWithTolerance,
    token: T.problemEditFormTokenByToken,
    literal: T.problemEditFormLiteral,
    custom: T.problemEditFormCustom,
  },
};

describe('Settings.vue', () => {
  it('Should handle problem settings', () => {
    const wrapper = shallowMount(problem_Settings, {
      propsData: baseSettingsProps,
    });

    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(0).text(),
    ).toContain('C, C++, C++11, C#, Haskell, Java, Pascal, Python, Ruby, Lua');
    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(1).text(),
    ).toContain('Karel');
    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(2).text(),
    ).toContain(T.wordsJustOutput);
    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(3).text(),
    ).toContain(T.wordsNoSubmissions);
  });

  it('Should handle problem settings with disabled elements', async () => {
    const wrapper = shallowMount(problem_Settings, {
      propsData: baseSettingsProps,
    });

    expect(
      wrapper.find('input[name="validator_time_limit"]').attributes('disabled'),
    ).toBe('disabled');

    const languages = <HTMLInputElement>(
      wrapper.find('select[name="languages"]').element
    );
    languages.value = 'cat';
    await languages.dispatchEvent(new Event('change'));

    const validator = <HTMLInputElement>(
      wrapper.find('select[name="validator"]').element
    );
    validator.value = 'custom';
    await validator.dispatchEvent(new Event('change'));

    expect(
      wrapper.find('input[name="validator_time_limit"]').attributes('disabled'),
    ).toBeFalsy();
    expect(
      (<HTMLInputElement>wrapper.find('select[name="languages"]').element)
        .value,
    ).toBe('cat');
    expect(
      (<HTMLInputElement>wrapper.find('select[name="validator"]').element)
        .value,
    ).toBe('custom');
  });
});
