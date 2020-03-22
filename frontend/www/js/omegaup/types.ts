import { omegaup } from './omegaup';
import { Optional } from 'typescript-optional';

export interface LinkableResource {
  toString(): string;
  getUrl(): string;
  getBadge(): Optional<string>;
}

export class ContestResult implements LinkableResource {
  alias: string = '';
  title: string = '';
  place: number = 0;

  constructor(contestResult: omegaup.ContestResult) {
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
    return Optional.ofNonNull(`${this.place}`);
  }
}

export class Problem implements LinkableResource {
  accepted?: number;
  alias: string = '';
  commit?: string;
  difficulty?: number;
  languages?: string;
  letter?: string;
  order?: number;
  penalty?: number;
  percent?: number;
  points?: number;
  quality?: number;
  ratio?: number;
  run_details?: omegaup.RunDetails;
  runs?: omegaup.CourseProblemRun[];
  score?: number;
  source?: string;
  statement?: omegaup.Statement;
  submissions?: number;
  templates?: string;
  tags?: omegaup.Tag[];
  title: string = '';
  version?: string;
  visibility?: number;
  visits?: number;

  constructor(problem: omegaup.Problem) {
    this.accepted = problem.accepted;
    this.alias = problem.alias;
    this.commit = problem.commit;
    this.difficulty = problem.difficulty;
    this.languages = problem.languages;
    this.letter = problem.letter;
    this.order = problem.order;
    this.penalty = problem.penalty;
    this.percent = problem.percent;
    this.points = problem.points;
    this.quality = problem.quality;
    this.ratio = problem.ratio;
    this.run_details = problem.run_details;
    this.runs = problem.runs;
    this.score = problem.score;
    this.source = problem.source;
    this.statement = problem.statement;
    this.submissions = problem.submissions;
    this.templates = problem.templates;
    this.tags = problem.tags;
    this.title = problem.title;
    this.version = problem.version;
    this.visibility = problem.visibility;
    this.visits = problem.visits;
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

  constructor(coderOfTheMonth: omegaup.SchoolCoderOfTheMonth) {
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

  constructor(
    classname: string,
    username: string,
    created_problems: number,
    solved_problems: number,
    organized_contests: number,
  ) {
    this.classname = classname;
    this.username = username;
    this.created_problems = created_problems;
    this.solved_problems = solved_problems;
    this.organized_contests = organized_contests;
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
