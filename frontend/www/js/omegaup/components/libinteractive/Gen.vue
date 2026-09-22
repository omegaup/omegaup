<template>
  <div class="card">
    <div class="card-body">
      <form
        action="/libinteractive/gen/"
        method="post"
        @submit="currentError = null"
      >
        <div class="mb-3">
          <label for="language">{{ T.libinteractiveLanguage }}</label>
          <select
            v-model="currentLanguage"
            class="form-select"
            name="language"
            :class="{ 'is-invalid': errorField === 'language' }"
            required
          >
            <option value="cpp">C++</option>
            <option value="c">C</option>
            <option value="java">Java</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="os">{{ T.libinteractiveOs }}</label>
          <select
            v-model="currentOs"
            class="form-select"
            name="os"
            :class="{ 'is-invalid': errorField === 'os' }"
            required
          >
            <option value="windows">Windows</option>
            <option value="unix">Linux/Mac OS</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="name">{{ T.libinteractiveIdlFilename }}</label>
          <input
            v-model="currentName"
            type="text"
            class="form-control"
            name="name"
            :class="{ 'is-invalid': errorField === 'name' }"
            required
          />
          <p>{{ T.libinteractiveIdlFilenameHelp }}</p>
        </div>
        <div class="mb-3">
          <label for="idl">IDL</label>
          <textarea
            v-model="currentIdl"
            class="form-control"
            rows="10"
            name="idl"
            :class="{ 'is-invalid': errorField === 'idl' }"
            required
          ></textarea>
        </div>
        <div class="mb-3 text-end">
          <button type="submit" class="btn btn-primary">
            <font-awesome-icon
              icon="cloud-download-alt"
              aria-hidden="true"
            ></font-awesome-icon>
            {{ T.wordsDownload }}
          </button>
        </div>
      </form>
    </div>
    <div v-if="errorDescription" class="card-body panel-footer">
      <pre><code class="w-100">{{ errorDescription }}</code></pre>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faCloudDownloadAlt } from '@fortawesome/free-solid-svg-icons';
library.add(faCloudDownloadAlt);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class LibinteractiveGen extends Vue {
  @Prop({ default: null }) error!: null | types.LibinteractiveError;
  @Prop() language!: string;
  @Prop() os!: string;
  @Prop() name!: string;
  @Prop() idl!: string;

  T = T;
  currentLanguage = this.language;
  currentOs = this.os;
  currentName = this.name;
  currentIdl = this.idl;
  currentError = this.error;

  get errorDescription(): null | string {
    return this.currentError?.description ?? null;
  }

  get errorField(): null | string {
    return this.currentError?.field ?? null;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
