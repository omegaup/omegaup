export * from './ui_transitional';
export * from './time';
export * from './markdown';

export function groupTypeahead(
  elem: HTMLElement,
  cb: (event: HTMLEvent, val: any) => void,
): void;
export function problemTypeahead(
  elem: HTMLElement,
  cb: (event: HTMLEvent, val: any) => void,
): void;
export function schoolTypeahead(
  elem: any,
  cb: (event: HTMLEvent, val: any) => void,
): void;
export function userTypeahead(
  elem: HTMLElement,
  cb: (event: HTMLEvent, val: any) => void,
): void;
