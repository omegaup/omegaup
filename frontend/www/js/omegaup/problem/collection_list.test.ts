import { collectionListURL, excludedTagsFromSearch } from './collection_list';

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
