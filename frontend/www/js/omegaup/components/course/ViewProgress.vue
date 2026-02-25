<template>
  <div class="container-fluid p-5">
    <div class="row">
      <div class="col-lg-10 mb-3">
        <div class="card">
          <div class="card-header">
            <h2>
              <a :href="`/course/${course.alias}/`">{{ course.name }}</a>
            </h2>
            <h6 class="mb-0">
              {{
                ui.formatString(T.studentsProgressRangeHeader, {
                  lowCount: (page - 1) * length + 1,
                  highCount: page * length,
                })
              }}
            </h6>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-fixed mb-0 d-block">
              <thead>
                <tr>
                  <th class="text-center align-middle">
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
                  <th class="text-center align-middle">
                    <span>
                      {{ T.courseProgressGlobalScore }}
                      <span class="d-block">{{
                        ui.formatString(
                          T.studentProgressDescriptionTotalPoints,
                          {
                            points: courseTotalPoints,
                            extraPoints: courseTotalExtraPoints,
                          },
                        )
                      }}</span>
                      <omegaup-common-sort-controls
                        column="total"
                        :sort-order="sortOrder"
                        :column-name="columnName"
                        @apply-filter="onApplyFilter"
                      ></omegaup-common-sort-controls>
                    </span>
                  </th>
                  <th
                    v-for="assignment in assignmentsProblems"
                    :key="assignment.alias"
                    class="score text-center align-middle"
                  >
                    <span>
                      {{ assignment.name }}
                      <span class="d-block"
                        >{{
                          assignment.extraPoints > 0
                            ? ui.formatString(
                                T.studentProgressDescriptionTotalPoints,
                                {
                                  points: assignment.points,
                                  extraPoints: assignment.extraPoints,
                                },
                              )
                            : ui.formatString(T.studentProgressPoints, {
                                points: assignment.points,
                              })
                        }}
                        <a
                          v-if="assignment.points === 0"
                          data-toggle="tooltip"
                          rel="tooltip"
                          :title="T.studentProgressOnlyLecturesDescription"
                          ><img
                            src="/media/question.png"
                            :alt="T.studentProgressOnlyLecturesDescription"
                        /></a>
                      </span>
                    </span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <omegaup-course-student-progress
                  v-for="student in sortedStudents"
                  :key="student.username"
                  :student-progress="student"
                  :course-alias="course.alias"
                  :assignments-problems="assignmentsProblems"
                >
                </omegaup-course-student-progress>
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            <omegaup-common-paginator
              :pager-items="pagerItems"
            ></omegaup-common-paginator>
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
              data-scorecard-csv-download-button
              class="btn btn-primary btn-sm w-100 my-1"
              :class="{ disabled: completeStudentsProgress === null }"
              :download="`${course.alias}.csv`"
              :href="csvDataUrl"
            >
              <div
                v-if="completeStudentsProgress === null"
                class="spinner-border"
                role="status"
              >
                <span class="sr-only">{{ T.spinnerLoadingMessage }}</span>
              </div>
              <span v-else>.csv</span>
            </a>
            <a
              class="btn btn-primary btn-sm w-100 my-1"
              :class="{ disabled: completeStudentsProgress === null }"
              :download="`${course.alias}.ods`"
              :href="odsDataUrl"
            >
              <div
                v-if="completeStudentsProgress === null"
                class="spinner-border"
                role="status"
              >
                <span class="sr-only">{{ T.spinnerLoadingMessage }}</span>
              </div>
              <span v-else>.ods</span>
            </a>
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
import common_SortControls from '../common/SortControls.vue';
import course_StudentProgress from './StudentProgress.vue';
import common_Paginator from '../common/Paginator.vue';
import { toCsv, TableCell, Percentage } from '../../csv';

Vue.use(AsyncComputedPlugin);

export function escapeXml(cell: TableCell): string {
  if (typeof cell !== 'string') return '';
  return cell
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/'/g, '&apos;')
    .replace(/"/g, '&quot;');
}

export function toOds(courseName: string, table: TableCell[][] | null): string {
  if (table === null) return '';
  let result = `<table:table table:name="${escapeXml(courseName)}">\n`;
  result += `<table:table-column table:number-columns-repeated="${table[0].length}"/>\n`;
  for (const row of table) {
    result += '<table:table-row>\n';
    for (const cell of row) {
      if (cell instanceof Percentage) {
        result += `<table:table-cell office:value-type="percentage" office:value="${cell.value.toFixed(
          4,
        )}"><text:p>${cell.toString()}</text:p></table:table-cell>`;
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
    'omegaup-course-student-progress': course_StudentProgress,
    'omegaup-common-paginator': common_Paginator,
  },
})
export default class CourseViewProgress extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop({ default: null })
  completeStudentsProgress!: types.StudentProgressInCourse[] | null;
  @Prop() students!: types.StudentProgressInCourse[];
  @Prop() assignmentsProblems!: types.AssignmentsProblemsPoints[];
  @Prop() pagerItems!: types.PageItem[];
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() totalRows!: number;

  T = T;
  ui = ui;
  sortOrder: omegaup.SortOrder = omegaup.SortOrder.Ascending;
  columnName = 'student';

  get sortedStudents(): types.StudentProgressInCourse[] {
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
      case 'total': {
        const sortFactor =
          this.sortOrder === omegaup.SortOrder.Descending ? 1 : -1;
        return this.students.sort(
          (a, b) => sortFactor * (a.courseProgress - b.courseProgress),
        );
      }
      default:
        return this.students.sort((a, b) =>
          a.username > b.username ? 1 : b.username > a.username ? -1 : 0,
        );
    }
  }

  get progressTable(): TableCell[][] | null {
    if (this.completeStudentsProgress === null) return null;
    const table: TableCell[][] = [];
    const header = [
      T.profileUsername,
      T.wordsName,
      T.courseProgressGlobalScore,
    ];
    header.push();
    for (const assignment of this.assignmentsProblems) {
      header.push(
        `${assignment.name} ${
          assignment.extraPoints > 0
            ? ui.formatString(T.studentProgressDescriptionTotalPoints, {
                points: assignment.points,
                extraPoints: assignment.extraPoints,
              })
            : ui.formatString(T.studentProgressPoints, {
                points: assignment.points,
              })
        }`,
      );
    }
    table.push(header);

    for (const student of this.completeStudentsProgress) {
      const row: TableCell[] = [
        student.username,
        student.name || '',
        new Percentage(student.courseProgress / 100),
      ];
      for (const assignment of this.assignmentsProblems) {
        row.push(
          assignment.alias in student.assignments
            ? new Percentage(
                student.assignments[assignment.alias].progress / 100,
              )
            : 0,
        );
      }
      table.push(row);
    }
    return table;
  }

  get csvDataUrl(): string {
    if (!this.progressTable) return '';
    return window.URL.createObjectURL(
      new Blob([toCsv(this.progressTable)], {
        type: 'text/csv;charset=utf-8;',
      }),
    );
  }

  get courseTotalPoints(): number {
    return this.assignmentsProblems.reduce((acc, curr) => acc + curr.points, 0);
  }

  get courseTotalExtraPoints(): number {
    return this.assignmentsProblems.reduce(
      (acc, curr) => acc + curr.extraPoints,
      0,
    );
  }

  @AsyncComputed()
  async odsDataUrl(): Promise<string> {
    if (!this.progressTable) return '';
    let zip = new JSZip();
    zip.file('mimetype', 'application/vnd.oasis.opendocument.spreadsheet', {
      compression: 'STORE',
    });
    let metaInf = zip.folder('META-INF');
    let table = this.progressTable;
    metaInf?.file(
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

<style lang="scss" scoped>
.sticky-offset {
  top: 4rem;
}

.table-fixed {
  max-height: 80vh;
  overflow: auto;

  thead {
    th {
      position: sticky;
      top: 0;
      z-index: 1;
      background: white;

      &:first-child {
        position: sticky;
        left: 0;
        background: white;
        z-index: 2;
      }
    }
  }

  tbody >>> th {
    position: sticky;
    left: 0;
    background: white;
    z-index: 1;
  }
}
</style>
