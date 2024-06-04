import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);
import { types } from './../api_types';
type caseExtension = 'err' | 'meta' | 'out';

export interface GraderOutputs {
  [key: `${string}.${caseExtension}`]: string;
}

export interface GraderRequest {
  input?: types.ProblemSettingsDistrib;
  language?: string;
  source?: string;
}

export interface GraderResults {
  compile_meta?: { [key: string]: types.RunMetadata };
  contest_score: number;
  groups?: types.RunDetailsGroup[];
  judged_by: string;
  max_score?: number;
  memory?: number;
  score: number;
  time?: number;
  verdict: string;
  wall_time?: number;
}

export interface GraderSessionStorageSources {
  language?: string;
  sources?: { [key: string]: string };
}

export interface GraderStore {
  alias?: string;
  compilerOutput?: string;
  currentCase?: string;
  dirty?: boolean;
  languages?: string[];
  logs?: string;
  max_score?: number;
  // three attributes: sample.err, sample.meta, sample.out
  // for all cases that are defined
  outputs?: GraderOutputs;
  // the recorded value is a boolean
  // but shouldn't it be an integer?
  problemsetId?: boolean;
  request?: GraderRequest;
  // what does result attribute hold?
  // the recorded value is null
  // this variable isn't used anywhere by IDE
  // probably safe to remove
  result?: any;
  results?: GraderResults;
  sessionStorageSources?: GraderSessionStorageSources;
  showSubmitButton?: boolean;
  updatingSettings?: boolean;
}
