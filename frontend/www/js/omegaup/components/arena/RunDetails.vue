<template>
  <form class="run-details-view">
    <div v-if="data">
      <button class="close">❌</button>
      <div class="cases" v-if="data.groups && data.feedback !== 'no'">
        <h3>{{ T.wordsCases }}</h3>
        <div></div>
        <table>
          <thead>
            <tr>
              <th>{{ T.wordsGroup }}</th>
              <th>{{ T.wordsCase }}</th>
              <th>{{ T.wordsVerdict }}</th>
              <th colspan="3" v-if="data.feedback === 'yes'">
                {{ T.rankScore }}
              </th>
              <th width="1"></th>
            </tr>
          </thead>
          <tbody v-for="element in data.groups">
            <tr class="group">
              <th class="center">{{ element.group }}</th>
              <th colspan="2">
                <div class="dropdown-cases" v-on:click="toggle(element.group)">
                  <span
                    v-bind:class="{
                      'glyphicon glyphicon-collapse-up':
                        groupVisible[element.group],
                      'glyphicon glyphicon-collapse-down': !groupVisible[
                        element.group
                      ],
                    }"
                  >
                  </span>
                </div>
              </th>
              <template v-if="data.feedback === 'yes'">
                <th class="score">
                  {{
                    element.contest_score
                      ? element.contest_score
                      : element.score
                  }}
                </th>
                <th class="center" width="10">
                  {{ element.max_score ? '/' : '' }}
                </th>
                <th>{{ element.max_score ? element.max_score : '' }}</th>
              </template>
            </tr>
            <tr
              v-for="problem in element.cases"
              v-if="groupVisible[element.group]"
            >
              <td></td>
              <td class="text-center">{{ problem.name }}</td>
              <td class="text-center">{{ problem.verdict }}</td>
              <template v-if="data.feedback === 'yes'">
                <td class="score">
                  {{
                    problem.contest_score
                      ? problem.contest_score
                      : problem.score
                  }}
                </td>
                <td class="center" width="10">
                  {{ problem.max_score ? '/' : '' }}
                </td>
                <td>{{ problem.max_score ? problem.max_score : '' }}</td>
              </template>
            </tr>
          </tbody>
        </table>
      </div>
      <h3>{{ T.wordsSource }}</h3>
      <a
        download="data.zip"
        v-bind:href="data.source"
        v-if="data.source_link"
        >{{ T.wordsDownload }}</a
      >
      <omegaup-arena-code-view
        v-bind:language="data.language"
        v-bind:readonly="true"
        v-bind:value="data.source"
        v-else
      ></omegaup-arena-code-view>
      <div class="compile_error" v-if="data.compile_error">
        <h3>{{ T.wordsCompilerOutput }}</h3>
        <pre class="compile_error" v-text="data.compile_error"></pre>
      </div>
      <div class="logs" v-if="data.logs">
        <h3>{{ T.wordsLogs }}</h3>
        <pre v-text="data.logs"></pre>
      </div>
      <div class="download">
        <h3>{{ T.wordsDownload }}</h3>
        <ul>
          <li>
            <a
              class="sourcecode"
              v-bind:download="data.source_name"
              v-bind:href="data.source_url"
              >{{ T.wordsDownloadCode }}</a
            >
          </li>
          <li>
            <a
              class="output"
              v-bind:href="'/api/run/download/run_alias/' + data.guid + '/'"
              v-if="data.problem_admin"
              >{{ T.wordsDownloadOutput }}</a
            >
          </li>
          <li>
            <a
              class="details"
              v-bind:href="
                '/api/run/download/run_alias/' + data.guid + '/complete/true/'
              "
              v-if="data.problem_admin"
              >{{ T.wordsDownloadDetails }}</a
            >
          </li>
        </ul>
      </div>
      <div class="judged_by" v-if="data.judged_by">
        <h3>{{ T.wordsJudgedBy }}</h3>
        <pre v-text="data.judged_by"></pre>
      </div>
    </div>
  </form>
</template>

<style lang="scss">
@import '../../../../sass/main.scss';

#overlay {
  display: none;
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9999999 !important;
  form {
    background: #eee;
    width: 80%;
    height: 90%;
    margin: auto;
    border: 2px solid #ccc;
    padding: 1em;
    position: absolute;
    overflow-y: auto;
    overflow-x: hidden;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    flex-direction: column;
    .close-container {
      width: 100%;
      .close {
        position: absolute;
        top: 0;
        right: 0;
        background-color: $omegaup-white;
        border: 1px solid #ccc;
        border-width: 0 0 1px 1px;
        font-size: 110%;
        width: 25px;
        height: 25px;
        &:hover {
          background-color: #eee;
        }
      }
    }
    .languages {
      width: 100%;
    }
    .filename-extension {
      width: 100%;
    }
    .run-submit-paste-text {
      width: 100%;
    }
    .code-view {
      width: 100%;
      flex-grow: 1;
      overflow: auto;
    }
    .upload-file {
      width: 100%;
    }
    .submit-run {
      width: 100%;
    }
  }
  input[type='submit'] {
    font-size: 110%;
    padding: 0.3em 0.5em;
  }
}
.dropdown-cases {
  height: 100%;
  width: 100%;
  margin: 0 auto;
  text-align: center;
  background: rgb(245, 245, 245);
  border-radius: 5px;
}
.vue-codemirror-wrap {
  height: 85%;
  .CodeMirror {
    height: 100%;
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import arena_CodeView from './CodeView.vue';

interface GroupVisibility {
  [name: string]: boolean;
}

@Component({
  components: {
    'omegaup-arena-code-view': arena_CodeView,
  },
})
export default class ArenaRunDetails extends Vue {
  @Prop() data!: omegaup.RunDetails;

  T = T;
  groupVisible: GroupVisibility = {};

  toggle(group: string): void {
    const visible = this.groupVisible[group];
    this.$set(this.groupVisible, group, !visible);
  }
}
</script>
