// A module that has test-mockable functions that interact with the global
// window.location.
enum ArenaPage {
  ContestVirtual,
  Contest,
  ContestPractice,
  Course,
}

export function getLocationHash(): string {
  return window.location.hash;
}

export function setLocationHash(hash: string): void {
  window.location.hash = hash;
}

export function getLocationHref(): string {
  return window.location.href;
}

export function setLocationHref({
  url,
  problemAlias,
  guid,
}: {
  url: string;
  problemAlias?: string;
  guid?: string;
}): void {
  const pathname = url.split('/');
  const arena = pathname[1];
  const problemsetAlias = pathname[2];
  let assignmentAlias: null | string = null;
  let arenaPage: ArenaPage = ArenaPage.Contest;
  // URL for courses
  if (arena === 'course') {
    arenaPage = ArenaPage.Course;
    assignmentAlias = pathname[4];
  } else if (pathname[3] && pathname[3] === 'practice') {
    // URL for contests in practice mode
    arenaPage = ArenaPage.ContestPractice;
  } else if (pathname[3] && pathname[3] === 'virtual') {
    // URL for contests in virtual mode
    arenaPage = ArenaPage.ContestVirtual;
  }
  const basePath = `/${arena}/${problemsetAlias}`;
  let path: null | string = null;
  if (!problemAlias) {
    switch (arenaPage) {
      case ArenaPage.Course:
        path = `${basePath}/assignment/${assignmentAlias}/#problems`;
        break;
      case ArenaPage.ContestPractice:
        path = `${basePath}/practice/#problems`;
        break;
      case ArenaPage.ContestVirtual:
        path = `${basePath}/virtual/#problems`;
        break;
      default:
        path = `${basePath}/#problems`;
    }
    window.history.pushState({ problemAlias: null }, document.title, path);
    return;
  }
  if (Boolean(problemAlias) && Boolean(guid)) {
    switch (arenaPage) {
      case ArenaPage.Course:
        path = `${basePath}/assignment/${assignmentAlias}/${problemAlias}/show-run/${guid}/#problems`;
        break;
      case ArenaPage.ContestPractice:
        path = `${basePath}/practice/${problemAlias}/show-run/${guid}/#problems`;
        break;
      case ArenaPage.ContestVirtual:
        path = `${basePath}/virtual/${problemAlias}/show-run/${guid}/#problems`;
        break;
      default:
        path = `${basePath}/${problemAlias}/show-run/${guid}/#problems`;
    }
  } else {
    switch (arenaPage) {
      case ArenaPage.Course:
        path = `${basePath}/assignment/${assignmentAlias}/${problemAlias}/#problems`;
        break;
      case ArenaPage.ContestPractice:
        path = `${basePath}/practice/${problemAlias}/#problems`;
        break;
      case ArenaPage.ContestVirtual:
        path = `${basePath}/virtual/${problemAlias}/#problems`;
        break;
      default:
        path = `${basePath}/${problemAlias}/#problems`;
    }
  }
  window.history.pushState({ problemAlias }, document.title, path);
}
