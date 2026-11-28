import Vue, { CreateElement } from 'vue';
import { collectionListURL, excludedTagsFromSearch } from './collection_list';

type CollectionListStub = Vue & {
  selectedTags: string[];
  excludedTags: string[];
  sortOrder: string;
  columnName: string;
  difficulty: string;
  quality: string;
  keyword: string;
};

// The handlers live inside the mocked module because the static import above
// runs collection_list.ts (and thus OmegaUp.on) before this file's own
// variables are initialized.
type MockedOmegaUp = { readyHandlers: Array<() => void> };

let mockCollectionList: CollectionListStub | null = null;

jest.mock('../omegaup', () => ({
  ...jest.requireActual('../omegaup'),
  OmegaUp: {
    readyHandlers: [] as Array<() => void>,
    on(this: MockedOmegaUp, _events: string, handler: () => void) {
      this.readyHandlers.push(handler);
    },
  },
}));

jest.mock('../components/problem/CollectionList.vue', () => ({
  name: 'omegaup-problem-collection-list-stub',
  props: [
    'selectedTags',
    'excludedTags',
    'keyword',
    'sortOrder',
    'columnName',
    'difficulty',
    'quality',
  ],
  render(this: CollectionListStub, createElement: CreateElement) {
    mockCollectionList = this;
    return createElement('div');
  },
}));

describe('problem collection list URL filters', () => {
  it.each([
    ['', []],
    [
      '?sort_order=asc&excluded_tag%5B%5D=problemTagArrays',
      ['problemTagArrays'],
    ],
    [
      '?excluded_tag%5B%5D=problemTagArrays&excluded_tag%5B%5D=problemTagLoops&tag%5B%5D=problemTagMatrices',
      ['problemTagArrays', 'problemTagLoops'],
    ],
    ['?excluded_tag=problemTagArrays', []],
  ])('Should read excluded tags from %s', (search, expectedTags) => {
    expect(excludedTagsFromSearch(search)).toEqual(expectedTags);
  });

  it('Should serialize included and excluded tags with the collection filters', () => {
    const url = collectionListURL(
      'problemLevelBasicIntroductionToProgramming',
      {
        language: 'es',
        query: 'matrices',
        columnName: 'title',
        sortOrder: 'asc',
        difficulty: 'easy',
        quality: 'all',
        selectedTags: ['problemTagArrays'],
        excludedTags: ['problemTagLoops', 'problemTagStrings'],
      },
    );

    const parsedUrl = new URL(url, 'https://omegaup.test');
    expect(parsedUrl.pathname).toBe(
      '/problem/collection/problemLevelBasicIntroductionToProgramming/',
    );
    expect(parsedUrl.searchParams.get('language')).toBe('es');
    expect(parsedUrl.searchParams.get('query')).toBe('matrices');
    expect(parsedUrl.searchParams.get('order_by')).toBe('title');
    expect(parsedUrl.searchParams.get('sort_order')).toBe('asc');
    expect(parsedUrl.searchParams.get('difficulty')).toBe('easy');
    expect(parsedUrl.searchParams.get('quality')).toBe('all');
    expect(parsedUrl.searchParams.getAll('tag[]')).toEqual([
      'problemTagArrays',
    ]);
    expect(parsedUrl.searchParams.getAll('excluded_tag[]')).toEqual([
      'problemTagLoops',
      'problemTagStrings',
    ]);
  });
});

const payload = {
  column: 'title',
  columns: [],
  difficulty: 'all',
  frequentTags: [],
  hideProblemTagsPreference: false,
  keyword: '',
  language: 'all',
  languages: [],
  level: 'problemLevelBasicIntroductionToProgramming',
  loggedIn: true,
  mode: 'asc',
  modes: [],
  pagerItems: [],
  problems: [],
  publicTags: [],
  selectedTags: ['problemTagArrays'],
  tagData: [],
  tagsList: [],
};

function mockedOmegaUp(): MockedOmegaUp {
  return jest.requireMock('../omegaup').OmegaUp;
}

type MockLocation = Pick<Location, 'search' | 'replace'>;

function loadPage(): CollectionListStub {
  jest.resetModules();
  const readyHandlers = mockedOmegaUp().readyHandlers;
  readyHandlers.length = 0;
  mockCollectionList = null;
  document.body.innerHTML =
    '<div id="main-container"></div><script id="payload" type="application/json"></script>';
  // jsdom does not implement innerText, which the payload parser reads.
  (document.getElementById(
    'payload',
  ) as HTMLElement).innerText = JSON.stringify(payload);
  // eslint-disable-next-line @typescript-eslint/no-var-requires
  require('./collection_list');
  expect(readyHandlers).toHaveLength(1);
  readyHandlers[0]();
  if (!mockCollectionList) {
    throw new Error('The collection list component was not rendered.');
  }
  return mockCollectionList;
}

describe('problem collection list page', () => {
  const originalLocation = window.location;
  const replace = jest.fn();
  let page: CollectionListStub | null = null;

  function load(search: string): CollectionListStub {
    const mockLocation: MockLocation = { search, replace };
    delete (window as { location?: Location }).location;
    (window as { location: MockLocation | Location }).location = mockLocation;
    page = loadPage();
    return page;
  }

  afterEach(() => {
    if (page) {
      page.$root.$destroy();
      page = null;
    }
    mockCollectionList = null;
    mockedOmegaUp().readyHandlers.length = 0;
    replace.mockReset();
    document.body.innerHTML = '';
    (window as {
      location: MockLocation | Location;
    }).location = originalLocation;
  });

  it('Should use the defaults when the URL has no filters', () => {
    const list = load('');

    expect(list.selectedTags).toEqual(['problemTagArrays']);
    expect(list.excludedTags).toEqual([]);
    expect(list.sortOrder).toBe('desc');
    expect(list.columnName).toBe('problem_id');
    expect(list.difficulty).toBe('all');
    expect(list.quality).toBe('onlyQualityProblems');
    expect(list.keyword).toBe('');
  });

  it('Should read the filters from the URL', () => {
    const list = load(
      '?sort_order=asc&order_by=title&language=es&difficulty=easy&quality=all&query=matrices&excluded_tag%5B%5D=problemTagLoops',
    );

    expect(list.sortOrder).toBe('asc');
    expect(list.columnName).toBe('title');
    expect(list.difficulty).toBe('easy');
    expect(list.quality).toBe('all');
    expect(list.excludedTags).toEqual(['problemTagLoops']);
  });

  it('Should redirect with the tags emitted by apply-filter, not the ones from the URL', () => {
    const list = load(
      '?language=es&query=matrices&excluded_tag%5B%5D=problemTagLoops',
    );

    list.$emit(
      'apply-filter',
      'title',
      'asc',
      'easy',
      'all',
      ['problemTagMatrices'],
      ['problemTagStrings'],
    );

    expect(replace).toHaveBeenCalledTimes(1);
    const url = new URL(replace.mock.calls[0][0], 'https://omegaup.test');
    expect(url.pathname).toBe(`/problem/collection/${payload.level}/`);
    expect(url.searchParams.get('language')).toBe('es');
    expect(url.searchParams.get('query')).toBe('matrices');
    expect(url.searchParams.get('order_by')).toBe('title');
    expect(url.searchParams.get('sort_order')).toBe('asc');
    expect(url.searchParams.get('difficulty')).toBe('easy');
    expect(url.searchParams.get('quality')).toBe('all');
    expect(url.searchParams.getAll('tag[]')).toEqual(['problemTagMatrices']);
    expect(url.searchParams.getAll('excluded_tag[]')).toEqual([
      'problemTagStrings',
    ]);
  });
});
