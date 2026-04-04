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
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

// Import Only Required Plugins
import { ButtonPlugin, CardPlugin, BIconCloudDownload } from 'bootstrap-vue';
import { types } from '../../api_types';
Vue.use(ButtonPlugin);
Vue.use(CardPlugin);

@Component({
  components: {
    BIconCloudDownload,
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
