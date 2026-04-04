export class Percentage {
  value: number;

  constructor(value: number) {
    this.value = value;
  }

  toString() {
    return `${(this.value * 100).toFixed(2)}%`;
  }
}

export type TableCell = undefined | null | number | string | Percentage;

export function escapeCsv(cell: TableCell): string {
  if (typeof cell === 'undefined' || cell === null) {
    return '';
  }
  if (cell instanceof Percentage) {
    cell = cell.toString();
  } else if (typeof cell === 'number') {
    cell = cell.toFixed(2);
  }
  if (typeof cell !== 'string') {
    cell = JSON.stringify(cell);
  }
  if (
    cell.indexOf(',') === -1 &&
    cell.indexOf('"') === -1 &&
    cell.indexOf("'") === -1
  ) {
    return cell;
  }
  return '"' + cell.replace('"', '""') + '"';
}

export function toCsv(table: TableCell[][] | null): string {
  if (table === null) {
    return '';
  }
  return table.map((row) => row.map(escapeCsv).join(',')).join('\r\n');
}
