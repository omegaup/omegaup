<template>
  <div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
      {{ T.certificateListMineTitle }}
    </h5>
    <div v-if="certificates.length === 0">
      <div class="my-2 empty-table-message">
        {{ T.certificateListMineCertificatesEmpty }}
      </div>
    </div>
    <table v-else class="table table-striped table-hover mb-0">
      <thead>
        <tr>
          <th scope="col" class="text-left align-middle">
            {{ T.certificateListMineDate }}
          </th>
          <th scope="col" class="text-left align-middle">
            {{ T.certificateListMineReason }}
          </th>
          <th scope="col" class="text-left align-middle d-none d-md-table-cell">
            {{ T.certificateListMineVerificationLink }}
          </th>
          <th scope="col" class="text-left align-middle">
            {{ T.certificateListMineActions }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(certificate, index) in certificates" :key="index">
          <td class="text-left align-middle">
            {{ certificate.date.toLocaleDateString() }}
          </td>
          <td class="text-left align-middle">
            {{ getReason(certificate.name, certificate.certificate_type) }}
          </td>
          <td class="text-left align-middle d-none d-md-table-cell">
            <span class="verification-link rounded border">
              {{ getVerificationLink(certificate.verification_code) }}
            </span>
          </td>
          <td class="d-flex justify-content-between align-items-center">
            <button
              v-clipboard="getVerificationLink(certificate.verification_code)"
              class="btn btn-primary copy-to-clipboard"
              type="button"
              :title="T.certificateListMineCopyToClipboard"
              :data-code="certificate.verification_code"
              @click="ui.success(T.certificateListMineLinkCopiedToClipboard)"
            >
              <font-awesome-icon icon="clipboard" />
            </button>
            <a
              class="btn btn-primary download-file"
              type="button"
              :href="getDownloadLink(certificate.verification_code)"
              :title="T.certificateListMineDownload"
              :data-code="certificate.verification_code"
              @click="ui.success(T.certificateListMineFileDownloaded)"
            >
              <font-awesome-icon icon="file-download" />
            </a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import Clipboard from 'v-clipboard';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);
Vue.use(Clipboard);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class Mine extends Vue {
  @Prop() certificates!: types.CertificateListItem[];
  @Prop() location!: string;

  T = T;
  ui = ui;

  getDownloadLink(verificationCode: string): string {
    return `${this.location}/certificate/${verificationCode}.pdf/`;
  }

  getVerificationLink(verificationCode: string): string {
    return `${this.location}/cert/${verificationCode}/`;
  }

  getReason(name: string | null, type: string): string {
    if (name !== null && type === 'contest') {
      return ui.formatString(T.certificateListMineContest, {
        contest_title: name,
      });
    }
    if (name !== null && type === 'course') {
      return ui.formatString(T.certificateListMineCourse, {
        course_name: name,
      });
    }
    if (type === 'coder_of_the_month') {
      return T.certificateListMineCoderOfTheMonth;
    }
    return T.certificateListMineCoderOfTheMonthFemale;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
span.verification-link {
  display: block;
  width: 90%;
  background-color: rgba(222, 222, 222, 0.4);
  padding: 0.5rem 0.5rem;
}
</style>
