<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-10 mb-3">
        <div class="omegaup-course-viewprogress card">
          <div class="card-header">
            <h2>
              <a :href="courseUrl">{{ course.name }}</a>
            </h2>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th class="text-center">
                    <span>
                      {{ T.wordsName }}
                      <omegaup-common-sort-controls
                        column="student"
                        :sort-order="sortOrder"
                        :column-name="columnName"
                        @apply-filter="onApplyFilter"
                      ></omegaup-common-sort-controls>
                    </span>
                  </th>
                  <th
                    v-for="assignment in assignments"
                    :key="assignment.alias"
                    class="score text-center"
                  >
                    <span>
                      {{ assignment.name }}<br />
                      {{ getTotalPoints(assignment) }}
                      <a
                        v-if="assignment.max_points === 0"
                        data-toggle="tooltip"
                        rel="tooltip"
                        :title="T.studentProgressOnlyLecturesDescription"
                        ><img src="/media/question.png"
                      /></a>
                      <!--<omegaup-common-sort-controls
                        :column="assignment.alias"
                        :sort-order="sortOrder"
                        :column-name="columnName"
                        @apply-filter="onApplyFilter"
                      ></omegaup-common-sort-controls>-->
                    </span>
                  </th>
                  <th class="text-center">
                    <span>
                      {{ T.courseProgressGlobalScore }}
                      <omegaup-common-sort-controls
                        column="total"
                        :sort-order="sortOrder"
                        :column-name="columnName"
                        @apply-filter="onApplyFilter"
                      ></omegaup-common-sort-controls>
                    </span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <omegaup-student-progress
                  v-for="student in orderedStudents"
                  :key="student.username"
                  :student="student"
                  :assignments="assignments"
                  :course="course"
                  :problem-titles="problemTitles"
                  :column-name="columnName"
                  :sort-order="sortOrder"
                  @apply-filter="onApplyFilter"
                >
                </omegaup-student-progress>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card sticky-top sticky-offset">
          <div class="card-header p-1">
            <p class="card-title text-sm-center mb-1">
              {{ T.courseStudentsProgressExportToSpreadsheet }}
            </p>
          </div>
          <div class="card-body">
            <a
              class="btn btn-primary btn-sm w-100 my-1"
              :download="csvFilename"
              :href="csvDataUrl"
              >.csv</a
            >
            <a
              class="btn btn-primary btn-sm w-100 my-1"
              :download="odsFilename"
              :href="odsDataUrl"
              >.ods</a
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import AsyncComputedPlugin from 'vue-async-computed';
import AsyncComputed from 'vue-async-computed-decorator';
import JSZip from 'jszip';
import StudentProgress from './StudentProgress.vue';
import common_SortControls from '../common/SortControls.vue';

Vue.use(AsyncComputedPlugin);

class Percentage {
  value: number;

  constructor(value: number) {
    this.value = value;
  }

  toString() {
    return `${(this.value * 100).toFixed(2)}%`;
  }
}

type TableCell = undefined | null | number | string | Percentage;

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

export function escapeXml(cell: TableCell): string {
  if (typeof cell !== 'string') return '';
  return cell
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/'/g, '&apos;')
    .replace(/"/g, '&quot;');
}

export function toCsv(table: TableCell[][]): string {
  return table.map((row) => row.map(escapeCsv).join(',')).join('\r\n');
}

export function toOds(courseName: string, table: TableCell[][]): string {
  let result = `<table:table table:name="${escapeXml(courseName)}">\n`;
  result += `<table:table-column table:number-columns-repeated="${table[0].length}"/>\n`;
  for (const row of table) {
    result += '<table:table-row>\n';
    for (const cell of row) {
      if (cell instanceof Percentage) {
        result += `<table:table-cell office:value-type="percentage" office:value="${cell.value}"><text:p>${cell}</text:p></table:table-cell>`;
      } else if (typeof cell === 'number') {
        const num: number = cell;
        result += `<table:table-cell office:value-type="float" office:value="${num}"><text:p>${num.toPrecision(
          2,
        )}</text:p></table:table-cell>`;
      } else {
        result += `<table:table-cell office:value-type="string"><text:p>${escapeXml(
          cell,
        )}</text:p></table:table-cell>`;
      }
    }
    result += '</table:table-row>\n';
  }
  result += '</table:table>';
  return result;
}

@Component({
  components: {
    'omegaup-common-sort-controls': common_SortControls,
    'omegaup-student-progress': StudentProgress,
  },
})
export default class CourseViewProgress extends Vue {
  @Prop() assignments!: omegaup.Assignment[];
  @Prop() course!: types.CourseDetails;
  @Prop() students!: types.StudentProgress[];
  @Prop() problemTitles!: { [key: string]: string };

  T = T;
  sortOrder: omegaup.SortOrder = omegaup.SortOrder.Ascending;
  columnName = 'student';

  score(
    student: types.StudentProgress,
    assignment: omegaup.Assignment,
  ): number {
    if (
      !Object.prototype.hasOwnProperty.call(student.score, assignment.alias)
    ) {
      return 0;
    }

    return Object.values(student.score[assignment.alias]).reduce(
      (accumulator: number, currentValue: number) => accumulator + currentValue,
      0,
    );
  }

  studentProgressUrl(student: types.StudentProgress): string {
    return `/course/${this.course.alias}/student/${student.username}/`;
  }

  get orderedStudents(): types.StudentProgress[] {
    switch (this.columnName) {
      case 'student':
        if (this.sortOrder === omegaup.SortOrder.Descending) {
          return this.students.sort((a, b) =>
            a.username > b.username ? 1 : b.username > a.username ? -1 : 0,
          );
        }
        return this.students.sort((a, b) =>
          a.username < b.username ? 1 : b.username < a.username ? -1 : 0,
        );
      case 'total':
        if (this.sortOrder === omegaup.SortOrder.Descending) {
          return this.students.sort((a, b) =>
            this.getGlobalScoreByStudent(a) > this.getGlobalScoreByStudent(b)
              ? 1
              : this.getGlobalScoreByStudent(b) >
                this.getGlobalScoreByStudent(a)
              ? -1
              : 0,
          );
        }
        return this.students.sort((a, b) =>
          this.getGlobalScoreByStudent(a) < this.getGlobalScoreByStudent(b)
            ? 1
            : this.getGlobalScoreByStudent(b) < this.getGlobalScoreByStudent(a)
            ? -1
            : 0,
        );
      default:
        return this.students.sort((a, b) =>
          a.username > b.username ? 1 : b.username > a.username ? -1 : 0,
        );
    }
  }

  get courseUrl(): string {
    return `/course/${this.course.alias}/`;
  }

  get progressTable(): TableCell[][] {
    const table: TableCell[][] = [];
    const header = [T.profileUsername, T.wordsName];
    for (const assignment of this.assignments) {
      header.push(assignment.name);
    }
    header.push(T.courseProgressGlobalScore);
    table.push(header);
    for (const student of this.students) {
      const row: TableCell[] = [student.username, student.name || ''];

      for (const assignment of this.assignments) {
        row.push(this.score(student, assignment));
      }
      row.push(this.getGlobalScoreByStudent(student));

      table.push(row);
    }
    return table;
  }

  get csvFilename(): string {
    return `${this.course.alias}.csv`;
  }

  get csvDataUrl() {
    let table = this.progressTable;
    let blob = new Blob([toCsv(table)], { type: 'text/csv;charset=utf-8;' });
    return window.URL.createObjectURL(blob);
  }

  get odsFilename(): string {
    return `${this.course.alias}.ods`;
  }

  get totalPoints(): number {
    return this.assignments
      .map((assignment) => assignment.max_points ?? 0)
      .reduce((acc, curr) => acc + curr, 0);
  }

  getTotalPoints(assignment: omegaup.Assignment): string {
    return ui.formatString(T.studentProgressDescriptionTotalPoints, {
      points: assignment.max_points,
    });
  }

  getGlobalScoreByStudent(student: types.StudentProgress): Percentage {
    const totalPoints = this.assignments
      .map((assignment) => assignment.max_points ?? 0)
      .reduce((acc, curr) => acc + curr, 0);
    if (!totalPoints) {
      return new Percentage(0);
    }

    return new Percentage(
      this.assignments
        .map((assignment) => this.score(student, assignment) / totalPoints)
        .reduce((acc, curr) => acc + curr, 0),
    );
  }

  @AsyncComputed()
  async odsDataUrl(): Promise<string> {
    let zip = new JSZip();
    zip.file('mimetype', 'application/vnd.oasis.opendocument.spreadsheet', {
      compression: 'STORE',
    });
    let metaInf = zip.folder('META-INF');
    let table = this.progressTable;
    metaInf.file(
      'manifest.xml',
      `<?xml version="1.0" encoding="UTF-8"?>
<manifest:manifest
    xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0"
    manifest:version="1.2">
 <manifest:file-entry manifest:full-path="/" manifest:version="1.2" manifest:media-type="application/vnd.oasis.opendocument.spreadsheet"/>
 <manifest:file-entry manifest:full-path="settings.xml" manifest:media-type="text/xml"/>
 <manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>
 <manifest:file-entry manifest:full-path="meta.xml" manifest:media-type="text/xml"/>
 <manifest:file-entry manifest:full-path="styles.xml" manifest:media-type="text/xml"/>
</manifest:manifest>`,
    );
    zip.file(
      'styles.xml',
      `<?xml version="1.0" encoding="UTF-8"?>
<office:document-styles
    xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
    office:version="1.2">
</office:document-styles>`,
    );
    zip.file(
      'settings.xml',
      `<?xml version="1.0" encoding="UTF-8"?>
<office:document-settings
    xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
    office:version="1.2">
</office:document-settings>`,
    );
    zip.file(
      'meta.xml',
      `<?xml version="1.0" encoding="UTF-8"?>
<office:document-meta
    xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
    xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
    office:version="1.2">
  <office:meta>
    <meta:generator>omegaUp</meta:generator>
  </office:meta>
</office:document-meta>`,
    );
    zip.file(
      'content.xml',
      `<?xml version="1.0" encoding="UTF-8"?>
<office:document-content
    xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
    xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
    xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
    office:version="1.2">
  <office:body>
    <office:spreadsheet>` +
        toOds(this.course.name, table) +
        `</office:spreadsheet>
  </office:body>
</office:document-content>`,
    );
    return window.URL.createObjectURL(
      await zip.generateAsync({
        type: 'blob',
        mimeType: 'application/ods',
        compression: 'DEFLATE',
      }),
    );
  }

  onApplyFilter(columnName: string, sortOrder: string): void {
    this.columnName = columnName;
    this.sortOrder =
      sortOrder === omegaup.SortOrder.Ascending
        ? omegaup.SortOrder.Ascending
        : omegaup.SortOrder.Descending;
  }
}
</script>

<style scoped>
.panel-body {
  overflow: auto;
  white-space: nowrap;
}
.sticky-offset {
  top: 4rem;
}
</style>
