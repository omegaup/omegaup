<template>
  <div class="card w-100 mb-4" data-versions>
    <div class="card-body controls">
      <label
        >{{ T.problemVersionDiffMode }}
        <select v-model="diffMode">
          <option value="files">
            {{ T.problemVersionDiffModeFiles }}
          </option>
          <option value="submissions">
            {{ T.problemVersionDiffModeSubmissions }}
          </option>
        </select></label
      >
      <label
        >{{ T.problemVersionShowOnlyChanges }}
        <input v-model="showOnlyChanges" type="checkbox"
      /></label>
    </div>
    <div class="card-body no-padding">
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
                <tr
                  v-for="revision in log"
                  :key="revision.commit"
                  :data-revision="revision.commit"
                  @click="selectedRevision = revision"
                >
                  <td>
                    <span
                      v-if="publishedRevision == revision"
                      :title="T.problemVersionPublishedRevision"
                      >✔️</span
                    >
                  </td>
                  <td>
                    <input
                      v-model="selectedRevision"
                      name="version"
                      type="radio"
                      :value="revision"
                    />
                  </td>

                  <td>
                    {{ time.formatDateTime(new Date(revision.committer.time))
                    }}<br />
                    <acronym :title="revision.commit"
                      ><tt>{{ revision.commit.substr(0, 8) }}</tt></acronym
                    >
                  </td>
                  <td>{{ revision.author.name }}</td>
                  <td>
                    <acronym :title="revision.version"
                      ><tt>{{ revision.version.substr(0, 8) }}</tt></acronym
                    >
                  </td>
                  <td>{{ revision.message }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-md-6 scrollable">
            <ul v-if="diffMode == 'files'" class="list-group no-margin">
              <li
                v-for="diffEntry in diffFiles"
                :key="diffEntry[0]"
                class="list-group-item"
                :class="diffEntry[1]"
              >
                {{ diffEntry[0] }}
              </li>
            </ul>
            <table
              v-if="diffMode == 'submissions'"
              class="table table-condensed"
            >
              <thead>
                <tr>
                  <th class="text-center" rowspan="2">GUID</th>
                  <th class="text-center" rowspan="2">
                    {{ T.problemVersionUsername }}
                  </th>
                  <th class="text-center" colspan="2">
                    {{ T.problemVersionCurrentVersion }}
                  </th>
                  <th class="text-center" colspan="2">
                    {{ T.problemVersionNewVersion }}
                  </th>
                </tr>
                <tr>
                  <th class="text-right">{{ T.rankScore }}</th>
                  <th class="text-center">{{ T.wordsVerdict }}</th>
                  <th class="text-right">{{ T.rankScore }}</th>
                  <th class="text-center">{{ T.wordsVerdict }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="diffEntry in diffSubmissions"
                  :key="diffEntry[0].guid"
                  :class="diffEntry[1]"
                >
                  <td class="text-center">
                    <acronym :title="diffEntry[0].guid"
                      ><tt>{{ diffEntry[0].guid.substr(0, 8) }}</tt></acronym
                    >
                  </td>
                  <td class="text-center">{{ diffEntry[0].username }}</td>
                  <td class="text-right">
                    {{ diffEntry[0].old_score.toFixed(2) }}
                  </td>
                  <td class="text-center">{{ diffEntry[0].old_verdict }}</td>
                  <td class="text-right">
                    {{ diffEntry[0].new_score.toFixed(2) }}
                  </td>
                  <td class="text-center">{{ diffEntry[0].new_verdict }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showFooter" class="card-footer">
      <form
        @submit.prevent="
          $emit('emit-select-version', selectedRevision, updatePublished)
        "
      >
        <button class="btn btn-primary" type="submit">
          {{ T.problemVersionUpdate }}
        </button>
        <select v-model="updatePublished">
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

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';

@Component
export default class ProblemVersions extends Vue {
  @Prop() log!: types.ProblemVersion[];
  @Prop({ default: null }) publishedRevision!: null | types.ProblemVersion;
  @Prop() showFooter!: boolean;
  @Prop({ default: null }) value!: null | types.ProblemVersion;

  T = T;
  time = time;
  diffMode = 'files';
  selectedRevision: null | types.ProblemVersion = this.value;
  runsDiff: types.CommitRunsDiff = {};
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
      if (!Object.prototype.hasOwnProperty.call(parentTree, path)) {
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
      if (Object.prototype.hasOwnProperty.call(tree, path)) {
        continue;
      }
      diff.push([path, 'list-group-item-danger']);
    }
    diff.sort();

    return diff;
  }

  get diffSubmissions(): [types.RunsDiff, string][] {
    if (!this.selectedRevision) {
      return [];
    }
    const version = this.selectedRevision.version;
    if (!Object.prototype.hasOwnProperty.call(this.runsDiff, version)) {
      return [];
    }
    const result: [types.RunsDiff, string][] = [];
    for (const row of this.runsDiff[version]) {
      let className = '';
      if (row.new_score && row.old_score && row.new_score > row.old_score) {
        className = 'success';
      } else if (
        row.new_score &&
        row.old_score &&
        row.new_score < row.old_score
      ) {
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
  onValueChange(newValue: types.ProblemVersion) {
    this.selectedRevision = newValue;
  }

  @Watch('selectedRevision')
  onSelectedRevisionChange(newValue: types.ProblemVersion) {
    this.$emit('input', this.selectedRevision);
    if (
      !newValue ||
      Object.prototype.hasOwnProperty.call(this.runsDiff, newValue.version)
    ) {
      return;
    }
    if (!this.showFooter) {
      this.$emit('runs-diff', this, this.selectedRevision);
    } else {
      this.$emit('emit-runs-diff', this, this.selectedRevision);
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.scrollable {
  max-height: 600px;
  overflow-y: auto;
}

.controls {
  border-bottom: 1px solid var(--problem-versions-controls-border-bottom-color);
  background-color: var(--problem-versions-controls-background-color);
}
</style>
