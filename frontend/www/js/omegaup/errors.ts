import T from './lang';

const _errors: Array<any> = [];

export function addError(error: any): void {
  _errors.push(error);
}

export function reportAnIssueURL(): string {
  // Not using UI.formatString() to avoid creating a circular
  // dependency.
  const issueBody = T.reportAnIssueTemplate
    .replace(
      '%(userAgent)',
      window && window.navigator ? window.navigator.userAgent : '(null)',
    )
    .replace(
      '%(referer)',
      window && window.location ? window.location.href : '(null)',
    )
    .replace('%(serializedErrors)', JSON.stringify(_errors))
    .replace(/\\n/g, '\n');
  return (
    'https://github.com/omegaup/omegaup/issues/new?body=' +
    encodeURIComponent(issueBody)
  );
}
