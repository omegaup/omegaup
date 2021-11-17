<template>
  <div class="root d-flex flex-row h-100">
    <div class="filenames">
      <div class="list-group">
        <button
          v-if="!zip"
          class="list-group-item list-group-item-action disabled"
          type="button"
        >
          <em>Empty</em>
        </button>
        <button
          v-for="(item, name) in zip.files"
          :key="name"
          v-else
          class="list-group-item list-group-item-action"
          type="button"
          :class="{ active: active == name }"
          :title="name"
          @click="select(item)"
        >
          {{ name }}
        </button>
      </div>
    </div>
    <textarea v-model="contents" class="editor" readonly></textarea>
  </div>
</template>

<script>
import * as Util from './util';

export default {
  data: () => ({
    zip: null,
    active: null,
    contents: '',
  }),
  methods: {
    select: function (item) {
      this.active = item.name;
      item
        .async('string')
        .then((value) => {
          this.contents = value;
        })
        .catch(Util.asyncError);
    },
  },
};
</script>

<style scoped>
div.filenames {
  overflow-y: auto;
}

button.list-group-item {
  width: 10em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

textarea {
  flex: 1;
}
</style>
