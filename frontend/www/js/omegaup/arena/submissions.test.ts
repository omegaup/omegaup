import { types } from '../api_types';
import { showSubmission, SubmissionRequest } from './submissions';

describe('submissions', () => {
  const mockCreateObjectURL = jest.fn();
  const mockRevokeObjectURL = jest.fn();
  global.URL.createObjectURL = mockCreateObjectURL;
  (global.URL as any).revokeObjectURL = mockRevokeObjectURL;

  beforeEach(() => {
    mockCreateObjectURL.mockReset();
    mockRevokeObjectURL.mockReset();
  });

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

  it('Should revoke previous Object URL when viewing a new submission', () => {
    const firstUrl = 'blob:http://localhost/first-url';
    const secondUrl = 'blob:http://localhost/second-url';
    mockCreateObjectURL
      .mockReturnValueOnce(firstUrl)
      .mockReturnValueOnce(secondUrl);

    const request: SubmissionRequest = {
      guid: '78099022574726af861839e1b4210188',
      isAdmin: false,
    };
    const runDetails: types.RunDetails = {
      admin: false,
      alias: 'test',
      cases: {},
      guid: '78099022574726af861839e1b4210188',
      language: 'py3',
      show_diff: 'none',
      feedback: [],
    };

    // First call — should create URL but not revoke anything
    const firstResult = showSubmission({ request, runDetails });
    expect(mockCreateObjectURL).toHaveBeenCalledTimes(1);
    expect(mockRevokeObjectURL).not.toHaveBeenCalled();
    expect(firstResult.source_url).toBe(firstUrl);

    // Second call — should revoke the first URL before creating a new one
    const secondResult = showSubmission({ request, runDetails });
    expect(mockCreateObjectURL).toHaveBeenCalledTimes(2);
    expect(mockRevokeObjectURL).toHaveBeenCalledTimes(1);
    expect(mockRevokeObjectURL).toHaveBeenCalledWith(firstUrl);
    expect(secondResult.source_url).toBe(secondUrl);
  });
});
