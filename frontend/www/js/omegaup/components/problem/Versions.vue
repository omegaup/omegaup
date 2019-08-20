<template>
  <div>
    <div class="panel-body controls">
      <label>{{ T.problemVersionDiffMode }} <select v-model="diffMode">
        <option value="files">
          {{ T.problemVersionDiffModeFiles }}
        </option>
        <option value="submissions">
          {{ T.problemVersionDiffModeSubmissions }}
        </option>
      </select></label> <label>{{ T.problemVersionShowOnlyChanges }} <input type="checkbox"
             v-model="showOnlyChanges"></label>
    </div>
    <div class="panel-body no-padding">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-6 scrollable">
            <table class="table table-striped no-margin">
              <thead>
                <tr>
                  <th></th>
                  <th></th>
                  <th>{{ T.problemVersionDate }}</th>
                  <th>{{ T.problemVersionUsername }}</th>
                  <th>{{ T.problemVersionVersion }}</th>
                  <th>{{ T.problemVersionMessage }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="revision in log"
                    v-on:click="selectedRevision = revision">
                  <td><span v-bind:title="T.problemVersionPublishedRevision"
                        v-if="publishedRevision == revision">✔️</span></td>
                  <td><input name="version"
                         type="radio"
                         v-bind:value="revision"
                         v-model="selectedRevision"></td>
                  <td>{{ UI.formatDateTime(new Date(revision.committer.time)) }}<br>
                  <acronym v-bind:title="revision.commit"><tt>{{ revision.commit.substr(0, 8)
                  }}</tt></acronym></td>
                  <td>{{ revision.author.name }}</td>
                  <td><acronym v-bind:title="revision.version"><tt>{{ revision.version.substr(0, 8)
                  }}</tt></acronym></td>
                  <td>{{ revision.message }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-md-6 scrollable">
            <ul class="list-group no-margin"
                v-if="diffMode == 'files'">
              <li class="list-group-item"
                  v-bind:class="diffEntry[1]"
                  v-for="diffEntry in diffFiles">{{ diffEntry[0] }}</li>
            </ul>
            <table class="table table-condensed"
                   v-if="diffMode == 'submissions'">
              <thead>
                <tr>
                  <th class="text-center"
                      rowspan="2">GUID</th>
                  <th class="text-center"
                      rowspan="2">{{ T.problemVersionUsername }}</th>
                  <th class="text-center"
                      colspan="2">{{ T.problemVersionCurrentVersion }}</th>
                  <th class="text-center"
                      colspan="2">{{ T.problemVersionNewVersion }}</th>
                </tr>
                <tr>
                  <th class="text-right">{{ T.rankScore }}</th>
                  <th class="text-center">{{ T.wordsVerdict }}</th>
                  <th class="text-right">{{ T.rankScore }}</th>
                  <th class="text-center">{{ T.wordsVerdict }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-bind:class="diffEntry[1]"
                    v-for="diffEntry in diffSubmissions">
                  <td class="text-center"><acronym v-bind:title="diffEntry[0].guid"><tt>{{
                  diffEntry[0].guid.substr(0, 8) }}</tt></acronym></td>
                  <td class="text-center">{{ diffEntry[0].username }}</td>
                  <td class="text-right">{{ diffEntry[0].old_score.toFixed(2) }}</td>
                  <td class="text-center">{{ diffEntry[0].old_verdict }}</td>
                  <td class="text-right">{{ diffEntry[0].new_score.toFixed(2) }}</td>
                  <td class="text-center">{{ diffEntry[0].new_verdict }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="panel-footer"
         v-if="showFooter">
      <form v-on:submit.prevent="$emit('select-version', selectedRevision, updatePublished)">
        <button class="btn btn-primary"
              type="submit">{{ T.problemVersionUpdate }}</button> <select v-model=
              "updatePublished">
          <option value="non-problemset">
            {{ T.problemVersionUpdatePublishedNonProblemset }}
          </option>
          <option value="owned-problemsets">
            {{ T.problemVersionUpdatePublishedOwnedProblemsets }}
          </option>
          <option value="editable-problemsets">
            {{ T.problemVersionUpdatePublishedEditableProblemsets }}
          </option>
        </select>
      </form>
    </div>
  </div>
</template>

<style>
.scrollable {
  max-height: 600px;
  overflow-y: auto;
}
.controls {
  border-bottom: 1px solid #ddd;
  background-color: #f5f5f5;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';

interface RunsDiff {
  guid: string;
  new_score: number;
  new_status: string;
  new_verdict: string;
  old_score: number;
  old_status: string;
  old_verdict: string;
  problemset_id: number;
  username: string;
}

interface CommitRunsDiff {
  [commit: string]: RunsDiff[];
}

@Component
export default class ProblemVersions extends Vue {
  @Prop() log!: omegaup.Commit[];
  @Prop() publishedRevision!: omegaup.Commit;
  @Prop() showFooter!: boolean;
  @Prop() value!: omegaup.Commit;

  T = T;
  UI = UI;
  diffMode = 'files';
  selectedRevision: omegaup.Commit = this.value;
  runsDiff: CommitRunsDiff = {};
  showOnlyChanges = false;
  updatePublished = 'owned-problemsets';

  get diffFiles(): string[][] {
    if (!this.selectedRevision || !this.publishedRevision) {
      return [];
    }
    const tree = this.selectedRevision.tree;
    const parentTree = this.publishedRevision.tree;
    if (!tree || !parentTree) {
      return [];
    }

    const diff = [];
    for (const path of Object.keys(tree)) {
      if (!parentTree.hasOwnProperty(path)) {
        diff.push([path, 'list-group-item-success']);
        continue;
      }
      if (parentTree[path] != tree[path]) {
        diff.push([path, 'list-group-item-warning']);
        continue;
      }
      if (!this.showOnlyChanges) {
        diff.push([path, '']);
      }
    }
    for (const path of Object.keys(parentTree)) {
      if (tree.hasOwnProperty(path)) {
        continue;
      }
      diff.push([path, 'list-group-item-danger']);
    }
    diff.sort();

    return diff;
  }

  get diffSubmissions(): [RunsDiff, string][] {
    if (!this.selectedRevision) {
      return [];
    }
    const version = this.selectedRevision.version;
    if (!this.runsDiff.hasOwnProperty(version)) {
      return [];
    }
    const result: [RunsDiff, string][] = [];
    for (const row of this.runsDiff[version]) {
      let className = '';
      if (row.new_score > row.old_score) {
        className = 'success';
      } else if (row.new_score < row.old_score) {
        className = 'danger';
      } else if (row.old_verdict != row.new_verdict) {
        className = 'warning';
      } else if (this.showOnlyChanges) {
        continue;
      }
      result.push([row, className]);
    }
    return result;
  }

  @Watch('value')
  onValueChange(newValue: omegaup.Commit, oldValue: omegaup.Commit) {
    this.selectedRevision = newValue;
  }

  @Watch('selectedRevision')
  onSelectedRevisionChange(newValue: omegaup.Commit, oldValue: omegaup.Commit) {
    this.$emit('input', this.selectedRevision);
    if (!newValue || this.runsDiff.hasOwnProperty(newValue.version)) {
      return;
    }
    this.$emit('runs-diff', this, this.selectedRevision);
  }
}

</script>
