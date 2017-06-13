<template>
  <div class="omegaup-course-viewprogress panel">
    <div class="page-header">
      <h2><a v-bind:href="courseUrl">{{ course.name }}</a></h2>
    </div>
    <div class="panel-body">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{ T.wordsName }}</th>
            <th class="score"
                v-for="assignment in assignments">{{ assignment.name }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students">
            <td>
              <a v-bind:href="studentProgressUrl(student)">{{ student.name || student.username
              }}</a>
            </td>
            <td class="score"
                v-for="assignment in assignments">{{ Math.round(score(student, assignment)) }}</td>
          </tr>
        </tbody>
      </table>
    </div><!-- panel-body -->
    <div class="panel-footer">
      {{ T.courseStudentsProgressExportToSpreadsheet }}: <a v-bind:download="csvFilename"
           v-bind:href="csvDataUrl">.csv</a> <a v-bind:download="odsFilename"
           v-bind:href="odsDataUrl">.ods</a>
    </div>
  </div><!-- panel -->
</template>

<script>
import AsyncComputed from 'vue-async-computed';
import JSZip from 'jszip';
import Vue from 'vue';

Vue.use(AsyncComputed);

function escapeCsv(cell) {
  if (typeof(cell) === 'undefined' || typeof(cell) === 'null') {
    return '';
  }
  if (typeof(cell) === 'number') {
    cell = cell.toPrecision(2);
  }
  if (typeof(cell) !== 'string') {
    cell = JSON.stringify(cell);
  }
  if (cell.indexOf(',') === -1 && cell.indexOf('"') === -1 &&
      cell.indexOf("'") === -1) {
    return cell;
  }
  return '"' + cell.replace('"', '""') + '"';
}

function escapeXml(str) {
  return str.replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/'/g, '&apos;')
      .replace(/"/g, '&quot;');
}

function toCsv(table) {
  return table.map((row) => row.map(escapeCsv).join(',')).join('\r\n');
}

function toOds(courseName, table) {
  let result = '<table:table table:name="' + escapeXml(courseName) + '">\n';
  result += '<table:table-column table:number-columns-repeated="' +
            table[0].length + '"/>\n';
  for (let row of table) {
    result += '<table:table-row>\n';
    for (let cell of row) {
      if (typeof cell === 'number') {
        result += '<table:table-cell office:value-type="float" office:value="' +
                  cell + '"><text:p>' + cell.toPrecision(2) +
                  '</text:p></table:table-cell>';
      } else {
        result += '<table:table-cell office:value-type="string"><text:p>' +
                  escapeXml(cell) + '</text:p></table:table-cell>';
      }
    }
    result += '</table:table-row>\n';
  }
  result += '</table:table>';
  return result;
}

export default {
  props: {
    T: Object,
    course: Object,
    students: Array,
    assignments: Array,
  },
  data: function() { return {};},
  methods: {
    score: function(student, assignment) {
      let score = student.progress[assignment.alias] || '0';
      return parseFloat(score);
    },
    studentProgressUrl: function(student) {
      return '/course/' + this.course.alias + '/student/' + student.username +
             '/';
    },
  },
  computed: {
    courseUrl: function() { return '/course/' + this.course.alias + '/';},
    progressTable: function() {
      let table = [];
      let header = [this.T.profileUsername, this.T.wordsName];
      for (let assignment of this.assignments) {
        header.push(assignment.name);
      }
      table.push(header);
      for (let student of this.students) {
        let row = [student.username, student.name];
        for (let assignment of this.assignments) {
          row.push(this.score(student, assignment));
        }
        table.push(row);
      }
      return table;
    },
    csvFilename: function() { return this.course.alias + '.csv';},
    csvDataUrl: function() {
      let table = this.progressTable;
      let blob = new Blob([toCsv(table)], {type: 'text/csv;charset=utf-8;'});
      return window.URL.createObjectURL(blob);
    },
    odsFilename: function() { return this.course.alias + '.ods';},
  },
  asyncComputed: {
    async odsDataUrl() {
      let zip = new JSZip();
      zip.file('mimetype', 'application/vnd.oasis.opendocument.spreadsheet', {
        compression: 'STORE',
      });
      let metaInf = zip.folder('META-INF');
      let table = this.progressTable;
      metaInf.file('manifest.xml', `<?xml version="1.0" encoding="UTF-8"?>
<manifest:manifest
    xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0"
    manifest:version="1.2">
 <manifest:file-entry manifest:full-path="/" manifest:version="1.2" manifest:media-type="application/vnd.oasis.opendocument.spreadsheet"/>
 <manifest:file-entry manifest:full-path="settings.xml" manifest:media-type="text/xml"/>
 <manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>
 <manifest:file-entry manifest:full-path="meta.xml" manifest:media-type="text/xml"/>
 <manifest:file-entry manifest:full-path="styles.xml" manifest:media-type="text/xml"/>
</manifest:manifest>`);
      zip.file('styles.xml', `<?xml version="1.0" encoding="UTF-8"?>
<office:document-styles
    xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
    office:version="1.2">
</office:document-styles>`);
      zip.file('settings.xml', `<?xml version="1.0" encoding="UTF-8"?>
<office:document-settings
    xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
    office:version="1.2">
</office:document-settings>`);
      zip.file('meta.xml', `<?xml version="1.0" encoding="UTF-8"?>
<office:document-meta
    xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
    xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
    office:version="1.2">
  <office:meta>
    <meta:generator>omegaUp</meta:generator>
  </office:meta>
</office:document-meta>`);
      zip.file('content.xml', `<?xml version="1.0" encoding="UTF-8"?>
<office:document-content
    xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
    xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
    xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
    office:version="1.2">
  <office:body>
    <office:spreadsheet>` + toOds(this.course.name, table) +
                                  `</office:spreadsheet>
  </office:body>
</office:document-content>`);
      return window.URL.createObjectURL(await zip.generateAsync({
        type: 'blob',
        mimeType: 'application/ods',
        compression: 'DEFLATE',
      }));
    },
  },
};

</script>

<style>
.omegaup-course-viewprogress td, .omegaup-course-viewprogress th {
  /* max-width 0 makes cell width proportional and allows content to overflow */
  max-width: 0;
  text-overflow: ellipsis;
  overflow: hidden;
}
.omegaup-course-viewprogress .score {
  text-align: right;
}
</style>
