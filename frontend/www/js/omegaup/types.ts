import omegaup from './api.js';

export interface LinkableResource {
  toString(): string;
  getUrl(): string;
}

export class Problem implements LinkableResource {
  accepted?: number;
  alias: string = '';
  commit?: string;
  difficulty?: number;
  languages?: string;
  letter?: string;
  order: number = 0;
  penalty?: number;
  percent?: number;
  points: number = 0;
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

  toString(): string {
    return this.title;
  }

  getUrl(): string {
    return `/arena/problem/${this.alias}`;
  }
}
