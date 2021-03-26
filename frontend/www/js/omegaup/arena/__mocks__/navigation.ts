import 'jest';

export const getLocationHash = jest
  .fn()
  .mockReturnValue('#problems/problem_alias/new-run');
export const isProblemAliasPresentInStore = jest.fn().mockReturnValue(true);
