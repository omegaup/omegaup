import omegaup from './api.js';

interface LinkableResource {
  toString(): string;
  getUrl(): string;
}

export class Problem implements LinkableResource {
  accepted?: number;
  alias: string;
  commit?: string;
  difficulty?: number;
  languages?: string;
  letter?: string;
  order: number;
  penalty?: number;
  percent?: number;
  points: number;
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
  title: string;
  version?: string;
  visibility?: number;
  visits?: number;

  constructor() {
    this.order = 0;
    this.title = '';
    this.alias = '';
    this.points = 0;
  }

  toString(): string {
    return this.title;
  }

  getUrl(): string {
    return `/arena/problem/${this.alias}`;
  }
}
