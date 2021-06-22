import { Optional } from 'typescript-optional';
import { types } from './api_types';

export interface DataContestResult {
  data: {
    alias: string;
    title: string;
  };
  length?: string;
  place: number;
}

export interface LinkableResource {
  toString(): string;
  getUrl(): string;
  getBadge(): Optional<string>;
}

export class ContestResult implements LinkableResource {
  alias: string = '';
  title: string = '';
  place: number = 0;

  constructor(contestResult: DataContestResult) {
    this.alias = contestResult.data.alias;
    this.title = contestResult.data.title;
    this.place = contestResult.place;
  }

  toString(): string {
    return this.title;
  }

  getUrl(): string {
    return `/arena/${this.alias}/`;
  }

  getBadge(): Optional<string> {
    if (!this.place) {
      return Optional.ofNonNull('—');
    }
    return Optional.ofNonNull(`${this.place}`);
  }
}

export class Problem implements LinkableResource {
  alias: string = '';
  title: string = '';

  constructor(problem: types.Problem) {
    this.alias = problem.alias;
    this.title = problem.title;
  }

  toString(): string {
    return this.title;
  }

  getUrl(): string {
    return `/arena/problem/${this.alias}/`;
  }

  getBadge(): Optional<string> {
    return Optional.empty();
  }
}

export class SchoolCoderOfTheMonth implements LinkableResource {
  classname: string = '';
  time: string = '';
  username: string = '';

  constructor(coderOfTheMonth: types.SchoolCoderOfTheMonth) {
    this.classname = coderOfTheMonth.classname;
    this.time = coderOfTheMonth.time;
    this.username = coderOfTheMonth.username;
  }

  toString(): string {
    return this.username;
  }

  getUrl(): string {
    return `/profile/${this.username}/`;
  }

  getBadge(): Optional<string> {
    return Optional.ofNonNull(this.time);
  }
}

export class SchoolUser implements LinkableResource {
  classname: string = '';
  username: string = '';
  created_problems: number = 0;
  organized_contests: number = 0;
  solved_problems: number = 0;
  displayField: string = 'solved_problems';

  constructor(user: types.SchoolUser) {
    this.classname = user.classname;
    this.username = user.username;
    this.created_problems = user.created_problems;
    this.solved_problems = user.solved_problems;
    this.organized_contests = user.organized_contests;
  }

  toString(): string {
    return this.username;
  }

  getUrl(): string {
    return `/profile/${this.username}/`;
  }

  getDisplayValue(): number {
    switch (this.displayField) {
      case 'solved_problems':
        return this.solved_problems;
      case 'organized_contests':
        return this.organized_contests;
      case 'created_problems':
        return this.created_problems;
      default:
        return 0;
    }
  }

  getBadge(): Optional<string> {
    return Optional.ofNonNull(`${this.getDisplayValue()}`);
  }
}
