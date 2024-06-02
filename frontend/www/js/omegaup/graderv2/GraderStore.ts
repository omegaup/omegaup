import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

// keep attirubte names as its, later we can fix inconsistencies
export interface GraderRequest {
  language?: string;
  source?: string;
  input?: any;
}
export interface GraderResults {
  compile_meta?: any;
  contest_score?: number;
  groups?: any[];
  judged_by?: string;
  max_score?: number;
  memory?: number;
  score?: number;
  time?: number;
  verdict?: string;
  wall_time?: number;
}
export interface GraderSessionStorageSources {
  language?: string;
  sources?: any;
}
export interface GraderStore {
  alias?: string;
  compilerOutput?: string;
  currentCase?: string;
  dirty?: boolean;
  languages?: string[];
  logs?: string;
  max_score?: number;
  outputs?: any;
  problemsetId?: boolean;
  request?: GraderRequest;
  result?: any;
  results?: GraderResults;
  sessionStorageSources?: GraderSessionStorageSources;
  showSubmitButton?: boolean;
  updatingSettings?: boolean;
}
