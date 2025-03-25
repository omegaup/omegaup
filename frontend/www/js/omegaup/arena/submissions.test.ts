import { types } from '../api_types';
import { showSubmission, SubmissionRequest } from './submissions';

describe('submissions', () => {
  global.URL.createObjectURL = jest.fn();
  it('Should handle showSubmissions function', () => {
    const request: SubmissionRequest = {
      guid: '78099022574726af861839e1b4210188',
      hash: '#problems/test/show-run:78099022574726af861839e1b4210188',
      isAdmin: false,
    };
    const runDetails: types.RunDetails = {
      admin: false,
      alias: 'test',
      cases: {
        statement_001: {
          in: '6\n2 3 2 3 2 4',
          out: '10',
        },
      },
      guid: '78099022574726af861839e1b4210188',
      language: 'py2',
      show_diff: 'none',
      feedback: [],
    };
    const formattedRunDetails = showSubmission({ request, runDetails });
    expect(formattedRunDetails).toEqual({
      admin: false,
      alias: 'test',
      cases: { statement_001: { in: '6\n2 3 2 3 2 4', out: '10' } },
      feedback: [],
      guid: '78099022574726af861839e1b4210188',
      judged_by: '',
      language: 'py2',
      logs: '',
      show_diff: 'none',
      source_link: false,
      source_name: 'Main.py2',
    });
  });
});
