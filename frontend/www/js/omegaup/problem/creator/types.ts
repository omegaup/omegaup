import { CasesState } from './modules/cases';

/**
 * StoreState
 * Store containing modules
 * @alias StoreState
 * @typedef {object}
 * @property {string} problemName Name of the problem
 * @property {CasesState} casesStore Module containing all the cases tab logic
 */
export interface StoreState {
  problemName: string;
  casesStore: CasesState;
}

/**
 * RootState
 * Store without modules
 * @alias RootState
 * @typedef {object}
 * @property {string} problemName Name of the problem
 */
export interface RootState {
  problemName: string;
}

/**
 * CaseLineData
 * Contains the type and the corresponding parameters for each type
 */
export type CaseLineData =
  | {
      kind: 'line';
      value: string;
    }
  | {
      kind: 'multiline';
      value: string | string[];
    }
  | {
      kind: 'array';
      size: number;
      min: number;
      max: number;
      distinct: boolean;
      arrayVal: number[];
    }
  | {
      kind: 'matrix';
      rows: number;
      cols: number;
      min: number;
      max: number;
      distinct: 'none' | 'rows' | 'cols' | 'both';
      matrixVal: number[][];
    };

/**
 * InLine
 * Line in the editor
 * @alias CaseLine
 * @typedef {object}
 * @property {string} lineId UUID of the line
 * @property {string} label Label of the line
 * @property {string} value Value of the line
 * @property {LineType} lineType Type of line
 * @property {ArrayData} arrayData Object containig all the logic for the Array Generator
 * @property {object} matrixData Object containig all the logic for the Matrix Generator
 */
export interface CaseLine {
  lineId: string;
  label: string;
  data: CaseLineData;
}

/**
 * Case
 * Contains all the information of a case
 * @alias Case
 * @typedef {object}
 * @property {string} caseId UUID of the case
 * @property {string} groupId UUID referencing to the parent group
 * @property {stirng} name Name of the case
 * @property {number} points Points of the case
 * @property {boolean} defined Whether the points are defined by the user or not
 * @property {Array<InLine>} lines Lines containing .IN information of the cases
 */
export interface Case {
  caseId: string;
  groupId: string;
  name: string;
  points: number;
  defined: boolean;
  lines: CaseLine[];
}

/**
 * Group
 * Contains all the information of a group
 * @alis Group
 * @typedef {object}
 * @property {string} groupId UUID of the group
 * @property {string} name Name of the group
 * @property {number} points Points of the group
 * @property {boolean} defined Whether the points are defined by the user or not
 * @property {Array<Case>} cases Cases of the group
 */
export interface Group {
  groupId: string;
  name: string;
  points: number;
  defined: boolean;
  cases: Case[];
}

/**
 * Option
 * Interface for <input type="select">
 * @alias Option
 * @typedef {object}
 * @property {string} value Value of the option
 * @property {string} text Display text of the option
 */
export interface Option {
  value: string;
  text: string;
}

/**
 * MultipleCaseAddRequest
 * Object containing all the information to add multiple cases in the store
 * @alias MultipleCaseAddRequest
 * @typedef {object}
 * @property {string} prefix Prefix of the name of all the cases
 * @property {string} suffix Suffix of the name of all the cases
 * @property {number} number Number of cases to add
 * @property {string} groupId UUID of the group
 */
export interface MultipleCaseAddRequest {
  prefix: string;
  suffix: string;
  number: number;
  groupId: string;
}

/**
 * CaseGroupID
 * Identifier of a case containing both groupId and caseId
 * @alias CaseGroupID
 * @typedef {object}
 * @property {string} groupId UUID of the group
 * @property {string} caseId UUID of the case
 */
export interface CaseGroupID {
  groupId: string;
  caseId: string;
}
