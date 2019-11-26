import omegaup from './api.js';
import { Optional } from "typescript-optional";

export interface LinkableResource {
  toString(): string;
  getUrl(): string;
  getBadge(): Optional<number>;
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

  getBadge(): Optional<number> {
    return Optional.ofNonNull(this.place);
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

  getBadge(): Optional<number> {
    return Optional.empty();
  }
}
