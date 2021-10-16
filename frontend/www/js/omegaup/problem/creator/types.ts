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
 * Linetype
 * Type of line in the editor
 * @alias Linetype
 * @typedef {string}
 */
export type LineType = 'line' | 'multiline' | 'array' | 'matrix';

/**
 * InLine
 * Line in the editor
 * @alias InLine
 * @typedef {object}
 * @property {string} lineId UUID of the line
 * @property {string} label Label of the line
 * @property {string} value Value of the line
 * @property {LineType} lineType Type of line
 * @property {ArrayData} arrayData Object containig all the logic for the Array Generator
 * @property {object} matrixData Object containig all the logic for the Matrix Generator
 */
export interface InLine {
  lineId: string;
  label: string;
  value: string;
  type: LineType;
  arrayData: ArrayData;
  matrixData: any;
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
  lines: InLine[];
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
 * MultipleCaseAdd
 * Object containing all the information to add multiple cases in the store
 * @alias MultipleCaseAdd
 * @typedef {object}
 * @property {string} prefix Prefix of the name of all the cases
 * @property {string} suffix Suffix of the name of all the cases
 * @property {number} number Number of cases to add
 * @property {string} groupId UUID of the group
 */
export interface MultipleCaseAdd {
  prefix: string;
  suffix: string;
  number: number;
  groupId: string;
}

/**
 * CaseGrouID
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

/**
 * Array Data
 * Array Data used in the Array Generator
 * @alias ArrayData
 * @typedef {object}
 * @property {size} size Size of the array
 * @property {number} min Minimum permitted value of the array
 * @property {number} max Maximum permitted value of the array
 * @property {boolean} distinct Whether the array values are distinct or not
 * @property {string} arrayVal Value of the array represented in a string
 *
 */
export interface ArrayData {
  size: number;
  min: number;
  max: number;
  distinct: boolean;
  arrayVal: string;
}
