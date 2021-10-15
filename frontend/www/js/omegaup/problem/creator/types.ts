import { CasesState } from './modules/cases';

export namespace types {
  export interface StoreState {
    problemName: string;
    casesStore: CasesState;
  }
  export interface RootState {
    problemName: string;
  }

  export type LineType = 'line' | 'multiline' | 'array' | 'matrix';

  export interface InLine {
    lineId: string;
    label: string;
    value: string;
    type: LineType;
    arrayData: ArrayData;
    matrixData: any;
  }

  export interface Case {
    caseId: string;
    groupId: string;
    name: string;
    points: number;
    defined: boolean;
    lines: InLine[];
  }

  export interface Group {
    groupId: string;
    name: string;
    points: number;
    defined: boolean;
    cases: Case[];
  }

  export interface Option {
    value: string;
    text: string;
  }

  export interface MultipleCaseAdd {
    prefix: string;
    suffix: string;
    number: number;
    groupId: string;
  }

  export interface CaseGroupID {
    groupId: string;
    caseId: string;
  }

  export interface ArrayData {
    size: number;
    min: number;
    max: number;
    distinct: boolean;
    arrayVal: string;
  }
}
