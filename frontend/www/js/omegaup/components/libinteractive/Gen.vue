<template>
  <b-card>
    <b-card-body>
      <form
        action="/libinteractive/gen/"
        method="post"
        @submit="currentError = null"
      >
        <div class="form-group">
          <label for="language">{{ T.libinteractiveLanguage }}</label>
          <select
            v-model="currentLanguage"
            class="custom-select"
            name="language"
            :class="{ 'is-invalid': errorField === 'language' }"
            required
          >
            <option value="cpp">C++</option>
            <option value="c">C</option>
            <option value="java">Java</option>
          </select>
        </div>
        <div class="form-group">
          <label for="os">{{ T.libinteractiveOs }}</label>
          <select
            v-model="currentOs"
            class="custom-select"
            name="os"
            :class="{ 'is-invalid': errorField === 'os' }"
            required
          >
            <option value="windows">Windows</option>
            <option value="unix">Linux/Mac OS</option>
          </select>
        </div>
        <div class="form-group">
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
        <div class="form-group">
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
        <div class="form-group text-right">
          <b-button variant="primary" type="submit">
            <b-icon-cloud-download aria-hidden="true"></b-icon-cloud-download>
            {{ T.wordsDownload }}
          </b-button>
        </div>
      </form>
    </b-card-body>
    <b-card-body v-if="errorDescription" class="panel-footer">
      <pre><code class="w-100">{{ errorDescription }}</code></pre>
    </b-card-body>
  </b-card>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import Vue from 'vue';
import T from '../../lang';

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

// Import Only Required Plugins
import { ButtonPlugin, CardPlugin, BIconCloudDownload } from 'bootstrap-vue';
import type { types } from '../../api_types';
Vue.use(ButtonPlugin);
Vue.use(CardPlugin);

export default defineComponent({
  name: 'LibinteractiveGen',
  components: {
    BIconCloudDownload,
  },
  props: {
    error: {
      type: Object as PropType<null | types.LibinteractiveError>,
      default: null,
    },
    language: {
      type: String as PropType<null | string>,
      default: null,
    },
    os: {
      type: String as PropType<null | string>,
      default: null,
    },
    name: {
      type: String as PropType<null | string>,
      default: null,
    },
    idl: {
      type: String as PropType<null | string>,
      default: null,
    },
  },
  data(): {
    T: typeof T;
    currentLanguage: null | string;
    currentOs: null | string;
    currentName: null | string;
    currentIdl: null | string;
    currentError: null | types.LibinteractiveError;
  } {
    return {
      T,
      currentLanguage: this.language,
      currentOs: this.os,
      currentName: this.name,
      currentIdl: this.idl,
      currentError: this.error,
    };
  },
  computed: {
    errorDescription(): null | string {
      return this.currentError?.description ?? null;
    },
    errorField(): null | string {
      return this.currentError?.field ?? null;
    },
  },
});
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
