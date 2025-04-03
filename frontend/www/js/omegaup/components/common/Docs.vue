<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.userDocsDocumentation }}</h2>
    </div>

    <!-- File Upload Section -->
    <div class="card-body">
      <label for="file-upload" class="btn btn-primary mb-2">
        <font-awesome-icon :icon="['fas', 'upload']" /> {{ T.userDocsUploadFile }}
      </label>
      <input
        id="file-upload"
        type="file"
        @change="onFileSelected"
        style="display: none;"
      />
      <button
        v-if="selectedFile"
        class="btn btn-success"
        @click="uploadSelectedFile"
      >
        {{ T.userDocsUpload }}
      </button>
      <span v-if="uploadMessage" class="text-success">{{ uploadMessage }}</span>
    </div>

    <!-- File List -->
    <ul v-for="(type, name) in docs" :key="name" class="list-group list-group-flush">
      <div class="h3">
        <font-awesome-icon :icon="getIcon(name)" :style="{ color: 'cornflowerblue' }" />
        {{ name }}
      </div>
      <li v-for="doc in type" :key="doc.name" class="list-group-item d-flex justify-content-between align-items-center">
        <a :href="doc.url">{{ doc.name }}</a>
        <button
          class="btn btn-danger btn-sm"
          @click="deleteFile(doc.name)"
        >
          <font-awesome-icon :icon="['fas', 'trash']" /> {{ T.userDocsDelete }}
        </button>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import { types } from '../../api_types';
import { User } from '../../api';
import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class CommonDocs extends Vue {
  @Prop() docs!: { [key: string]: types.UserDocument[] };

  T = T;
  ui = ui;
  selectedFile: File | null = null;
  uploadMessage = '';

  getIcon(name: number | string): string[] {
    const icon = ['fas'];
    if (name === 'pdf') {
      icon.push('file-pdf');
    } else if (name === 'md') {
      icon.push('file');
    } else {
      icon.push('folder');
    }
    return icon;
  }

  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.selectedFile = input.files[0];
    }
  }

  async uploadSelectedFile() {
  if (!this.selectedFile) return;

  const reader = new FileReader();
  reader.onload = async () => {
    try {
      // Read the file content as a Data URL (base64 encoded)
      const base64String = (reader.result as string).split(',')[1];

      const response = await User.uploadFile({
        filename: this.selectedFile!.name,
        content: base64String,
      });

      this.uploadMessage = response.message;
      this.selectedFile = null;
      await this.$emit('file-uploaded');
    } catch (error) {
      ui.apiError(error);
    }
  };
  // Read the file as a Data URL
  reader.readAsDataURL(this.selectedFile);
}


  async deleteFile(filename: string) {
    try {
      await User.deleteFile({ filename });
      await this.$emit('file-deleted'); // Emit event to refresh file list
      ui.success(T.userDocsDeleteSuccess);
    } catch (error) {
      console.log("redereshshsh",error);
      ui.apiError(error);
    }
  }

  mounted() {
    // Initialize the component
    console.log('CommonDocs component mounted...11122');
  }
}
</script>

<style scoped>
.card-body {
  margin-bottom: 1rem;
}
.btn {
  margin-right: 0.5rem;
}
</style>
