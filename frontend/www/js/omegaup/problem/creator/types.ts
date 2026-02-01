import { CasesState } from './modules/cases';

export type CaseID = string;
export type GroupID = string;
export type LineID = string;
export type LineInfoID = string;
export type LayoutID = string;

/**
 * StoreState
 * Store containing modules
 * @alias StoreState
 * @typedef {object}
 * @property {string} problemName Name of the problem
 * @property {string} problemMarkdown Markdown of the problem
 * @property {string} problemCodeContent Content of the code file
 * @property {string} problemCodeExtension Extebsion of the code file
 * @property {string} problemSolutionMarkdown Markdown of the solution to the problem
 * @property {CasesState} casesStore Module containing all the cases tab logic
 */
export interface StoreState {
  problemName: string;
  problemMarkdown: string;
  problemCodeContent: string;
  problemCodeExtension: string;
  problemSolutionMarkdown: string;
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
 * CaseLineKind
 * Contains the possible values of Caseline kind
 */
export type CaseLineKind = 'line' | 'multiline' | 'array' | 'matrix';

/**
 * MatrixDistinctType
 * Defines the different ways matrix can be distinct
 */
export enum MatrixDistinctType {
  None = 'none',
  Rows = 'rows',
  Cols = 'cols',
  Both = 'both',
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
      value: string;
    }
  | {
      kind: 'array';
      size: number;
      min: number;
      max: number;
      distinct: boolean;
      value: string;
    }
  | {
      kind: 'matrix';
      rows: number;
      cols: number;
      min: number;
      max: number;
      distinct: MatrixDistinctType;
      value: string;
    };

/**
 * CaseLine
 * Line in the editor
 * @alias CaseLine
 * @typedef {object}
 * @property {LineID} lineID UUID of the line
 * @property {CaseID | null} caseID UUID referencing to the parent case
 * @property {string} label Label of the line
 * @property {CaseLineData} data data of the line
 */
export interface CaseLine {
  lineID: LineID;
  caseID: CaseID | null;
  label: string;
  data: CaseLineData;
}

/**
 * CaseLifeInfo
 * Info of a CaseLine
 * @alias CaseLineInfo
 * @typedef {object}
 * @property {LineInfoID} lineInfoID UUID of the lineInfo
 * @property {string} label Label of the line
 * @property {CaseLineData} content content of the line
 */
export interface CaseLineInfo extends Omit<CaseLine, 'lineID' | 'caseID'> {
  lineInfoID: LineInfoID;
}

/**
 * Case
 * Contains all the information of a case
 * @alias Case
 * @typedef {object}
 * @property {CaseID} caseID UUID of the case
 * @property {GroupID} groupID UUID referencing to the parent group
 * @property {string} name Name of the case
 * @property {number | null} points Points of the case
 * @property {Array<CaseLine>} lines Lines containing .IN information of the cases
 * @property {string} output output of the case
 * @property {boolean} autoPoints Whether the points are gonna be calculated automatically
 */
export interface Case {
  caseID: string;
  groupID: string;
  name: string;
  lines: CaseLine[];
  output: string;
  points: number;
  autoPoints: boolean;
}

/**
 * Group
 * Contains all the information of a group
 * @alias Group
 * @typedef {object}
 * @property {GrouID} groupID UUID of the group
 * @property {string} name Name of the group
 * @property {number} points Points of the group
 * @property {boolean} autoPoints Whether the points are gonna be calculated automatically
 * @property {boolean} ungroupedCase Whether this case belongs to an ungrouped case
 * @property {Array<Case>} cases Cases of the group
 */
export interface Group {
  groupID: GroupID;
  name: string;
  points: number;
  autoPoints: boolean;
  ungroupedCase: boolean;
  cases: Case[];
}

/**
 * Layout
 * Contains all the information of a Layout
 * @alias Group
 * @typedef {object}
 * @property {LayoutID} layoutID UUID of the layout
 * @property {string} name Name of the Layout
 * @property {Array<CaseLineInfo>} caseLineInfos Line infos of the Layout
 */
export interface Layout {
  layoutID: LayoutID;
  name: string;
  caseLineInfos: CaseLineInfo[];
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
 * @property {number} numberOfCases Number of cases to add
 * @property {GroupID} groupID UUID of the group
 */
export interface MultipleCaseAddRequest {
  prefix: string;
  suffix: string;
  numberOfCases: number;
  groupID: GroupID;
}

export interface CaseRequest {
  groupID: GroupID;
  caseID: CaseID;
  name: string;
  autoPoints: boolean;
  points: number;
  lines?: CaseLine[];
}

/**
 * CaseGroupID
 * Identifier of a case containing both groupID and caseID
 * @alias CaseGroupID
 * @typedef {object}
 * @property {GroupID} groupID UUID of the group
 * @property {GroupID} caseID UUID of the case
 */
export interface CaseGroupID {
  groupID: GroupID;
  caseID: GroupID;
}

export type AddTabTypes = 'case' | 'group' | 'multiplecases';
