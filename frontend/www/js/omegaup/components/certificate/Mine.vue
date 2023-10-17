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
    <table
      v-else
      class="table table-striped table-hover table-responsive-sm mb-0"
    >
      <thead>
        <tr>
          <th scope="col" class="text-left align-middle">
            {{ T.certificateListMineDate }}
          </th>
          <th scope="col" class="text-left align-middle">
            {{ T.certificateListMineReason }}
          </th>
          <th scope="col" class="text-left align-middle">
            {{ T.certificateListMineDownload }}
          </th>
          <th scope="col" class="text-left align-middle">
            {{ T.certificateListMineVerificationLink }}
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
          <td class="text-left align-middle">
            <a
              class="btn btn-primary"
              type="button"
              :href="getDownloadLink(certificate.verification_code)"
            >
              {{ T.certificateListMineDownload }}
            </a>
          </td>
          <td class="text-left align-middle">
            <button
              v-clipboard="getVerificationLink(certificate.verification_code)"
              class="btn btn-primary"
              type="button"
            >
              {{ T.certificateListMineCopyToClipboard }}
            </button>
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
Vue.use(Clipboard);

@Component
export default class Mine extends Vue {
  @Prop() certificates!: types.CertificateListItem[];

  T = T;

  getDownloadLink(verificationCode: string): string {
    return `${window.location.origin}/certificate/${verificationCode}.pdf/`;
  }

  getVerificationLink(verificationCode: string): string {
    return `${window.location.origin}/cert/${verificationCode}/`;
  }

  getReason(name: string | null, type: string): string {
    if (name != null) {
      return name;
    }
    if (type == 'coder_of_the_month') {
      return T.certificateListMineCoderOfTheMonth;
    }
    return T.certificateListMineCoderOfTheMonthFemale;
  }
}
</script>
