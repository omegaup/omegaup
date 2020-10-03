<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-10">
        <div class="omegaup-course-viewprogress card">
          <div class="card-header">
            <h2>
              <a v-bind:href="courseUrl">{{ course.name }}</a>
            </h2>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th class="text-center">{{ T.wordsName }}</th>
                  <th
                    v-bind:key="assignment.alias"
                    class="score text-center"
                    v-for="assignment in assignments"
                  >
                    {{ assignment.name }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <omegaup-student-progress
                  v-for="student in students"
                  v-bind:key="student.username"
                  v-bind:student="student"
                  v-bind:assignments="assignments"
                  v-bind:course="course"
                >
                </omegaup-student-progress>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-sm-2">
        <div class="card sticky-top sticky-offset">
          <div class="card-header p-1">
            <p class="card-title text-sm-center mb-1">
              {{ T.courseStudentsProgressExportToSpreadsheet }}
            </p>
          </div>
          <div class="card-body">
            <a
              class="btn btn-primary btn-sm mr-1"
              v-bind:download="csvFilename"
              v-bind:href="csvDataUrl"
              >.csv</a
            >
            <a
              class="btn btn-primary btn-sm"
              v-bind:download="odsFilename"
              v-bind:href="odsDataUrl"
              >.ods</a
            >
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- panel -->
</template>

<style>
.panel-body {
  overflow: auto;
  white-space: nowrap;
}
.sticky-offset {
  top: 4rem;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import AsyncComputedPlugin from 'vue-async-computed';
import AsyncComputed from 'vue-async-computed-decorator';
import JSZip from 'jszip';
import StudentProgress from './StudentProgress.vue';

Vue.use(AsyncComputedPlugin);

export function escapeCsv(cell: undefined | number | string): string {
  if (typeof cell === 'undefined') {
    return '';
  }
  if (typeof cell === 'number') {
    cell = Math.round(cell);
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

export function escapeXml(cell: undefined | string | null): string {
  if (typeof cell !== 'string') return '';
  return cell
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/'/g, '&apos;')
    .replace(/"/g, '&quot;');
}

export function toCsv(table: (number | string)[][]): string {
  return table.map((row) => row.map(escapeCsv).join(',')).join('\r\n');
}

export function toOds(
  courseName: string,
  table: (number | string)[][],
): string {
  let result = `<table:table table:name="${escapeXml(courseName)}">\n`;
  result += `<table:table-column table:number-columns-repeated="${table[0].length}"/>\n`;
  for (const row of table) {
    result += '<table:table-row>\n';
    for (const cell of row) {
      if (typeof cell === 'number') {
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
  components: { 'omegaup-student-progress': StudentProgress },
})
export default class CourseViewProgress extends Vue {
  @Prop() assignments!: omegaup.Assignment[];
  @Prop() course!: types.CourseDetails;
  @Prop() students!: types.StudentProgress[];

  T = T;

  score(
    student: types.StudentProgress,
    assignment: omegaup.Assignment,
  ): number {
    if (!student.score.hasOwnProperty(assignment.alias)) {
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

  get courseUrl(): string {
    return `/course/${this.course.alias}/`;
  }

  get progressTable(): (number | string)[][] {
    const table: (number | string)[][] = [];
    const header = [T.profileUsername, T.wordsName];
    for (const assignment of this.assignments) {
      header.push(assignment.name);
    }
    table.push(header);
    for (const student of this.students) {
      const row: (number | string)[] = [student.username, student.name || ''];

      for (const assignment of this.assignments) {
        row.push(this.score(student, assignment));
      }

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
}
</script>
