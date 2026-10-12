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
          <td
            v-if="selectedCertificate === certificate.verification_code"
            class="text-left align-middle border-selected"
          >
            {{ certificate.date.toLocaleDateString() }}
          </td>
          <td v-else class="text-left align-middle">
            {{ certificate.date.toLocaleDateString() }}
          </td>
          <td class="text-left align-middle">
            {{ getReason(certificate.name, certificate.certificate_type) }}
          </td>
          <td class="text-left align-middle d-none d-md-table-cell">
            <span class="bg-light rounded border p-2 d-block w-100">
              {{ getVerificationLink(certificate.verification_code) }}
            </span>
          </td>
          <td class="d-flex align-items-center">
            <button
              v-clipboard="getVerificationLink(certificate.verification_code)"
              copy-to-clipboard
              class="btn btn-primary mr-2"
              type="button"
              :title="T.certificateListMineCopyToClipboard"
              :data-code="certificate.verification_code"
              @click="onCopyVerificationLink"
            >
              <font-awesome-icon icon="clipboard" />
            </button>
            <a
              download-file
              class="btn btn-primary"
              type="button"
              :href="getDownloadLink(certificate.verification_code)"
              :title="T.certificateListMineDownload"
              :data-code="certificate.verification_code"
              @click="onDownloadCertificate"
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
import Vue, { defineComponent } from 'vue';
import type { PropType } from 'vue';
import Clipboard from 'v-clipboard';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);
Vue.use(Clipboard);

export default defineComponent({
  name: 'CertificateMine',
  components: {
    'font-awesome-icon': FontAwesomeIcon,
  },
  props: {
    certificates: {
      type: Array as PropType<types.CertificateListItem[]>,
      required: true,
    },
    selectedCertificate: {
      type: String,
      default: undefined,
    },
    location: {
      type: String,
      required: true,
    },
  },
  emits: ['show-copy-message', 'show-download-message'],
  data() {
    return {
      T,
      ui,
    };
  },
  methods: {
    getDownloadLink(verificationCode: string): string {
      return `${this.location}/certificate/${verificationCode}.pdf/`;
    },
    getVerificationLink(verificationCode: string): string {
      return `${this.location}/cert/${verificationCode}/`;
    },
    getReason(name: string | null, type: string): string {
      if (name === null) {
        return type === 'coder_of_the_month'
          ? T.certificateListMineCoderOfTheMonth
          : T.certificateListMineCoderOfTheMonthFemale;
      }
      if (type === 'contest') {
        return ui.formatString(T.certificateListMineContest, {
          contest_title: name,
        });
      }
      return ui.formatString(T.certificateListMineCourse, {
        course_name: name,
      });
    },
    onCopyVerificationLink(): void {
      this.$emit('show-copy-message');
    },
    onDownloadCertificate(): void {
      this.$emit('show-download-message');
    },
  },
});
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.border-selected {
  border-left: 0.25rem solid
    var(--certificates-selected-certificate-border-color);
}
</style>
