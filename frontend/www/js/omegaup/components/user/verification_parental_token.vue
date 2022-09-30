const modalTemplate = 
    <v-dialog @keydown.esc="modalOptions.persistent ? () => {} : closeModal()" :persistent="modalOptions.persistent" scrollable v-model="showDialog" :fullscreen="modalOptions.fullscreen !== undefined ? modalOptions.fullscreen : $vuetify.breakpoint.xsOnly" :max-width="typeof modalOptions.maxWidth == 'number' ? (modalOptions.maxWidth + 'px') : modalOptions.maxWidth"> +
      <v-card class="pa-0" :style="styles"> +
        <v-icon v-if="!modalOptions.persistent" class="ma-2" @click="closeModal()" style="position: absolute; top: 4px; right: 5px; z-index: 1000;">close</v-icon> +
        <ModalComponent :modal-data="modalData" :show-dialog.sync="showDialog"/> +
      </v-card> +
    </v-dialog>


<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';