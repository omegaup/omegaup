/* eslint-disable */
import Vuex, { StoreOptions } from 'vuex';
import Vue from 'vue';

import * as Util from './util';
import * as templates from './GraderTemplates';
import { types } from './../api_types';

export type CaseKey = `${string}.${'err' | 'meta' | 'out'}`;
export interface GraderOutputs {
  [key: CaseKey]: string;
}

export interface GraderRequest {
  input: types.ProblemSettingsDistrib;
  language: string;
  source: string;
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
  language: string;
  sources: { [key: string]: string };
}

export interface GraderStore {
  alias: string;
  compilerOutput: string;
  currentCase: string;
  dirty: boolean;
  languages: string[];
  logs: string;
  outputs: GraderOutputs;
  problemsetId: boolean;
  request: GraderRequest;
  results: GraderResults;
  sessionStorageSources: GraderSessionStorageSources | null;
  showSubmitButton: boolean;
  updatingSettings: boolean;
}
// this type does not exist in types
export interface SettingsCase {
  Name: string;
  Cases: { Name: string; Weight: number }[];
  Weight: number;
}

const languageSelectElement = document.getElementById(
  'language',
) as HTMLSelectElement;

const persistToSessionStorage = Util.throttle(
  ({
    alias,
    contents,
  }: {
    alias: string;
    contents: GraderSessionStorageSources | null;
  }) => {
    if (!contents) return;
    sessionStorage.setItem(
      `ephemeral-sources-${alias}`,
      JSON.stringify(contents),
    );
  },
  1000,
);

const defaultValidatorSource = templates.defaultValidatorSource;
const defaultInteractiveIdlSource = templates.defaultInteractiveIdlSource;
const defaultInteractiveMainSource = templates.defaultInteractiveMainSource;

const sourceTemplates = { ...templates.sourceTemplates };
const originalInteractiveTemplates = {
  ...templates.originalInteractiveTemplates,
};
const interactiveTemplates = { ...originalInteractiveTemplates };

const languageExtensionMapping: Record<string, string> = {};
Object.keys(Util.supportedLanguages).forEach((key) => {
  languageExtensionMapping[key] = Util.supportedLanguages[key].extension;
});

Vue.use(Vuex);
const storeOptions: StoreOptions<GraderStore> = {
  state: {
    alias: '',
    compilerOutput: '',
    currentCase: '',
    dirty: true,
    languages: [],
    logs: '',
    outputs: {},
    problemsetId: false,
    request: {
      input: {
        cases: {},
        limits: {
          ExtraWallTime: '0s',
          MemoryLimit: 33554432,
          OutputLimit: 10240,
          OverallWallTimeLimit: '1s',
          TimeLimit: '1s',
        },
        validator: {
          name: 'token-caseless',
        },
      },
      language: '',
      source: '',
    },
    results: {
      compile_meta: {},
      contest_score: 0,
      groups: [],
      judged_by: '',
      max_score: 0,
      memory: 0,
      score: 0,
      time: 0,
      verdict: '',
      wall_time: 0,
    },
    sessionStorageSources: null,
    showSubmitButton: false,
    updatingSettings: false,
  },
};
const store = new Vuex.Store<GraderStore>(storeOptions);
