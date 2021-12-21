import { render, fireEvent, screen, waitFor } from '@testing-library/vue';
import { types } from '../../api_types';
import T from '../../lang';
import Form from './Form.vue';
const props: types.ProblemFormPayload = {
  title: 'title',
  alias: 'title',
  validator: 'token',
  emailClarifications: false,
  source: 'title',
  visibility: 0,
  statusError: '',
  allowUserAddTags: true,
  showDiff: 'none',
  timeLimit: 1000,
  validatorTimeLimit: 1000,
  overallWallTimeLimit: '',
  extraWallTime: 0,
  outputLimit: 10240,
  inputLimit: 10240,
  memoryLimit: 32768,
  levelTags: [],
  visibilityStatuses: {},
  languages:
    'c11-clang,c11-gcc,cpp11-clang,cpp11-gcc,cpp17-clang,cpp17-gcc,cs,hs,java,lua,pas,py2,py3,rb',
  tags: [
    {
      name: 'problemLevelBasicKarel',
    },
  ],
  problem_level: '',
  publicTags: ['problemTagInputAndOutput'],

  validatorTypes: {
    'token-caseless': 'Token por token, ignorando mayúsculas/minúsculas',
    'token-numeric': 'Tokens numéricos con tolerancia de 1e-9',
    token: 'Token por Token',
    literal: 'Interpretar salida estándar como puntaje',
    custom: 'Validador personalizado (validator.$lang$)',
  },
  validLanguages: {
    'c11-clang,c11-gcc,cpp11-clang,cpp11-gcc,cpp17-clang,cpp17-gcc,cs,hs,java,lua,pas,py2,py3,rb':
      'C, C++, C++11, C#, Haskell, Java, Pascal, Python, Ruby, Lua',
    'kj,kp': 'Karel',
    cat: T.wordsJustOutput,
    '': T.wordsNoSubmissions,
  },
};

test('Should open a collapsed panel', async () => {
  render(Form, { props: { data: props } });
  expect(screen.getByText(T.problemEditLimits)).toHaveClass('collapsed');
  await fireEvent.click(screen.getByText(T.problemEditLimits));
  waitFor(() =>
    expect(screen.getByText(T.problemEditLimits)).not.toHaveClass('collapsed'),
  );
});

test('Should open a collapsed panel when they are closed and the user clicks submit', async () => {
  render(Form, { props: { data: props } });
  expect(screen.getByText(T.problemEditBasicInfo)).not.toHaveClass('collapsed');
  expect(screen.getByText(T.problemEditTags)).not.toHaveClass('collapsed');
  await fireEvent.click(screen.getByText(T.problemEditTags));
  await fireEvent.click(screen.getByText(T.problemEditBasicInfo));
  waitFor(() =>
    expect(screen.getByText(T.problemEditBasicInfo)).toHaveClass('collapsed'),
  );
  waitFor(() =>
    expect(screen.getByText(T.problemEditTags)).toHaveClass('collapsed'),
  );
  const submitButton = screen.getAllByText(T.problemEditFormCreateProblem)[1];
  await fireEvent.submit(submitButton);
  waitFor(() =>
    expect(screen.getByText(T.problemEditBasicInfo)).not.toHaveClass(
      'collapsed',
    ),
  );
  waitFor(() =>
    expect(screen.getByText(T.problemEditTags)).not.toHaveClass('collapsed'),
  );
});
