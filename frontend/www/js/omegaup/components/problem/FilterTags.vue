<template>
  <div class="card filter-tags-card">
    <div class="card-body">
      <h3 class="section-font-size text-center">
        {{ T.problemEditAddTags }}
        <a
          v-tooltip="T.problemCollectionTagsFilterTooltip"
          tabindex="0"
          :aria-label="T.problemCollectionTagsFilterTooltip"
        >
          <img src="/media/question.png" :alt="T.problemEditAddTags" />
        </a>
      </h3>
      <div class="tag-filter-grid">
        <div class="tag-filter-header">
          <span></span>
          <span>+</span>
          <span>−</span>
        </div>
        <div v-for="tag in tags" :key="tag.name" class="tag-filter-row">
          <span>{{ `${T[tag.name]} (${tag.problemCount})` }}</span>
          <input
            v-model="currentSelectedTags"
            :value="tag.name"
            type="checkbox"
            :aria-label="`+ ${T[tag.name]}`"
          />
          <input
            v-model="excludedTags"
            :value="tag.name"
            type="checkbox"
            :aria-label="`− ${T[tag.name]}`"
          />
        </div>
      </div>
      <div class="form-group mt-2">
        <vue-typeahead-bootstrap
          :data="publicQualityTagNames"
          :serializer="publicQualityTagsSerializer"
          :placeholder="T.collecionOtherTags"
          @hit="addOtherTag"
        >
        </vue-typeahead-bootstrap>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';

import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';

@Component({
  components: {
    'vue-typeahead-bootstrap': VueTypeaheadBootstrap,
  },
  directives: {
    tooltip: VTooltip,
  },
})
export default class FilterTags extends Vue {
  @Prop() publicQualityTags!: types.TagWithProblemCount[];
  @Prop({ default: () => [] }) tags!: types.TagWithProblemCount[];
  @Prop({ default: () => [] }) selectedTags!: string[];
  @Prop({ default: () => [] }) selectedExcludedTags!: string[];

  T = T;
  currentSelectedTags = [...this.selectedTags];
  excludedTags: string[] = [...this.selectedExcludedTags];
  emitScheduled = false;

  get publicQualityTagNames(): string[] {
    return this.publicQualityTags.map((x) => x.name);
  }

  addOtherTag(tag: string): void {
    if (!this.currentSelectedTags.includes(tag)) {
      this.currentSelectedTags.push(tag);
    }
  }

  publicQualityTagsSerializer(name: string): string {
    if (Object.prototype.hasOwnProperty.call(T, name)) {
      return T[name];
    }
    return name;
  }

  @Watch('currentSelectedTags')
  onNewTagSelected(): void {
    if (this.excludedTags.some((t) => this.currentSelectedTags.includes(t))) {
      this.excludedTags = this.excludedTags.filter(
        (t) => !this.currentSelectedTags.includes(t),
      );
    }
    this.scheduleEmit();
  }

  @Watch('excludedTags')
  onNewExcludedTag(): void {
    if (this.currentSelectedTags.some((t) => this.excludedTags.includes(t))) {
      this.currentSelectedTags = this.currentSelectedTags.filter(
        (t) => !this.excludedTags.includes(t),
      );
    }
    this.scheduleEmit();
  }

  scheduleEmit(): void {
    if (this.emitScheduled) return;
    this.emitScheduled = true;
    this.$nextTick(() => {
      this.emitScheduled = false;
      this.$emit('filters-changed', {
        selected: this.currentSelectedTags,
        excluded: this.excludedTags,
      });
    });
  }
}
</script>

<style scoped>
.section-font-size {
  font-size: 1.44rem;
}

.tag-filter-header,
.tag-filter-row {
  display: grid;
  grid-template-columns: 1fr 2.5rem 2.5rem;
  align-items: center;
  column-gap: 0.5rem;
}

.tag-filter-header {
  text-align: center;
  font-size: 1.25rem;
  border-bottom: 1px solid
    var(--problem-collection-list-tag-filter-border-color);
  padding-bottom: 0.25rem;
  margin-bottom: 0.5rem;
}

.tag-filter-header span:nth-child(2) {
  color: var(--problem-collection-list-tag-filter-include-color);
}

.tag-filter-header span:nth-child(3) {
  color: var(--problem-collection-list-tag-filter-exclude-color);
}

.tag-filter-row {
  padding: 0.3rem 0;
}

.tag-filter-row input[type='checkbox'] {
  justify-self: center;
  width: 1.25rem;
  height: 1.25rem;
  cursor: pointer;
}

.tag-filter-row input[type='checkbox']:nth-child(2) {
  accent-color: var(--problem-collection-list-tag-filter-include-color);
}

.tag-filter-row input[type='checkbox']:nth-child(3) {
  accent-color: var(--problem-collection-list-tag-filter-exclude-color);
}

.filter-tags-card {
  margin-bottom: 1rem;
}
</style>
