const _errors: Array<any> = [];

export function addError(error: any): void {
  _errors.push(error);
}

export function registerReportAnIssue(reportAnIssueTemplate: string): void {
  const reportAnIssue = <HTMLAnchorElement>(
    document.getElementById('report-an-issue')
  );
  if (
    !reportAnIssue ||
    !window.navigator ||
    !window.navigator.userAgent ||
    !reportAnIssueTemplate
  ) {
    return;
  }
  reportAnIssue.addEventListener('click', (event: Event): void => {
    // Not using UI.formatString() to avoid creating a circular
    // dependency.
    let issueBody = reportAnIssueTemplate
      .replace('%(userAgent)', window.navigator.userAgent)
      .replace('%(referer)', window.location.href)
      .replace('%(serializedErrors)', JSON.stringify(_errors))
      .replace(/\\n/g, '\n');
    reportAnIssue.href =
      'https://github.com/omegaup/omegaup/issues/new?body=' +
      encodeURIComponent(issueBody);
  });
}
