<template>
  <div class="omegaup-admin-carousel card">
    <div class="card-header">
      <h2 class="card-title">{{ T.omegaupTitleCarouselManagement }}</h2>
      <button
        class="btn btn-primary float-right"
        @click.prevent="showCreateModal = true"
      >
        <font-awesome-icon :icon="['fas', 'plus']" />
        {{ T.carouselCreateNew }}
      </button>
    </div>
    <div class="card-body">
      <div class="mb-2">
        <label class="mr-2">{{ T.wordsLanguage }}:</label>
        <select
          v-model="currentLanguage"
          class="form-control d-inline-block"
          style="width: auto"
        >
          <option value="en">English</option>
          <option value="es">Español</option>
          <option value="pt">Português</option>
        </select>
        <label class="mr-2 ml-3">{{ T.wordsStatus }}:</label>
        <select
          v-model="currentStatusFilter"
          class="form-control d-inline-block"
          style="width: auto"
        >
          <option value="active">{{ T.wordsActive }}</option>
          <option value="archived">{{ T.wordsArchived }}</option>
          <option value="all">{{ T.wordsAll }}</option>
        </select>
      </div>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{ T.wordsTitle }}</th>
            <th>{{ T.carouselExcerpt }}</th>
            <th>{{ T.carouselImageUrl }}</th>
            <th>{{ T.carouselLink }}</th>
            <th>{{ T.carouselButtonTitle }}</th>
            <th>{{ T.carouselExpirationDate }}</th>
            <th>{{ T.wordsStatus }}</th>
            <th>{{ T.wordsEdit }}</th>
            <th>{{ T.wordsArchived }}</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in filteredCarouselItems"
            :key="item.carousel_item_id"
          >
            <td>{{ getMultilingualText(item.title, currentLanguage) }}</td>
            <td>
              {{
                truncateText(
                  getMultilingualText(item.excerpt, currentLanguage),
                  50,
                )
              }}
            </td>
            <td>
              <img
                v-if="item.image_url"
                :src="item.image_url"
                :alt="getMultilingualText(item.title, currentLanguage)"
                style="max-width: 100px; max-height: 60px"
              />
              <span v-else>{{ T.carouselNoImage }}</span>
            </td>
            <td>
              <a :href="item.link" target="_blank">
                <!-- {{
                truncateText(item.link, 20)
              }} -->
              <font-awesome-icon :icon="['fas', 'link']" /></a>
            </td>
            <td>
              {{ getMultilingualText(item.button_title, currentLanguage) }}
            </td>
            <td>
              {{
                item.expiration_date
                  ? formatDate(item.expiration_date)
                  : T.carouselNoExpiration
              }}
            </td>
            <td>
              <span
                :class="{
                  'badge badge-success': item.status,
                  'badge badge-secondary': !item.status,
                }"
              >
                {{ item.status ? T.wordsActive : T.wordsInactive }}
              </span>
            </td>
            <td>
              <button
                class="btn btn-sm btn-primary mr-1"
                @click.prevent="editItem(item)"
              >
                <font-awesome-icon :icon="['fas', 'edit']" />
              </button>
            </td>
            <td>
              <button
                class="btn btn-sm btn-danger"
                @click.prevent="confirmDelete(item)"
              >
                <font-awesome-icon :icon="['fas', 'archive']" />
              </button>
            </td>
          </tr>
          <tr v-if="carouselItems.length === 0">
            <td colspan="8" class="text-center">
              {{ T.carouselNoItems }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <div
      v-if="showCreateModal || showEditModal"
      class="modal show d-block"
      tabindex="-1"
      role="dialog"
      @click.self="closeModal"
    >
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ showEditModal ? T.carouselEditItem : T.carouselCreateNew }}
            </h5>
            <button
              type="button"
              class="close"
              aria-label="Close"
              @click.prevent="closeModal"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveItem">
              <!-- Language Tabs -->
              <ul class="nav nav-tabs mb-3" role="tablist">
                <li v-for="lang in languages" :key="lang.code" class="nav-item">
                  <a
                    class="nav-link"
                    :class="{ active: editingLanguage === lang.code }"
                    href="#"
                    @click.prevent="editingLanguage = lang.code"
                  >
                    {{ lang.name }}
                  </a>
                </li>
              </ul>

              <!-- Multilingual Fields -->
              <div v-for="lang in languages" :key="lang.code">
                <div v-show="editingLanguage === lang.code">
                  <div class="form-group">
                    <label>{{ T.wordsTitle }} ({{ lang.name }}) *</label>
                    <input
                      v-model="multilingualData.title[lang.code]"
                      type="text"
                      class="form-control"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label>{{ T.carouselExcerpt }} ({{ lang.name }}) *</label>
                    <textarea
                      v-model="multilingualData.excerpt[lang.code]"
                      class="form-control"
                      rows="3"
                      required
                    ></textarea>
                  </div>
                  <div class="form-group">
                    <label
                      >{{ T.carouselButtonTitle }} ({{ lang.name }}) *</label
                    >
                    <input
                      v-model="multilingualData.button_title[lang.code]"
                      type="text"
                      class="form-control"
                      required
                    />
                  </div>
                </div>
              </div>

              <!-- Non-multilingual Fields -->
              <div class="form-group">
                <label>{{ T.carouselImageUrl }} *</label>
                <input
                  v-model="currentItem.image_url"
                  type="url"
                  class="form-control"
                  required
                />
              </div>
              <div class="form-group">
                <label>{{ T.carouselLink }} *</label>
                <input
                  v-model="currentItem.link"
                  type="url"
                  class="form-control"
                  required
                />
              </div>
              <div class="form-group">
                <label>{{ T.carouselExpirationDate }}</label>
                <input
                  v-model="expirationDateInput"
                  type="datetime-local"
                  class="form-control"
                />
                <small class="form-text text-muted">
                  {{ T.carouselExpirationDateHint }}
                </small>
              </div>
              <div class="form-group">
                <div class="form-check">
                  <input
                    v-model="currentItem.status"
                    type="checkbox"
                    class="form-check-input"
                  />
                  <label class="form-check-label">
                    {{ T.wordsActive }}
                  </label>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click.prevent="closeModal"
            >
              {{ T.wordsCancel }}
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click.prevent="saveItem"
            >
              {{ showEditModal ? T.wordsUpdate : T.wordsCreate }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div
      v-if="showCreateModal || showEditModal"
      class="modal-backdrop show"
    ></div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="modal show d-block"
      tabindex="-1"
      role="dialog"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ T.carouselDeleteConfirm }}</h5>
            <button
              type="button"
              class="close"
              aria-label="Close"
              @click.prevent="showDeleteModal = false"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>{{ T.carouselDeleteMessage }}</p>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click.prevent="showDeleteModal = false"
            >
              {{ T.wordsCancel }}
            </button>
            <button
              type="button"
              class="btn btn-danger"
              @click.prevent="deleteItem"
            >
              {{ T.wordsDelete }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showDeleteModal" class="modal-backdrop show"></div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

interface MultilingualData {
  title: { [key: string]: string };
  excerpt: { [key: string]: string };
  button_title: { [key: string]: string };
}

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class Carousel extends Vue {
  @Prop() carouselItems!: types.CarouselItem[];

  T = T;
  showCreateModal = false;
  showEditModal = false;
  showDeleteModal = false;
  currentItem: types.CarouselItem = this.getEmptyItem();
  itemToDelete: number | null = null;
  expirationDateInput = '';
  currentLanguage = 'en';
  editingLanguage = 'en';
  currentStatusFilter = 'active';
  languages = [
    { code: 'en', name: 'English' },
    { code: 'es', name: 'Español' },
    { code: 'pt', name: 'Português' },
  ];
  multilingualData: MultilingualData = {
    title: { en: '', es: '', pt: '' },
    excerpt: { en: '', es: '', pt: '' },
    button_title: { en: '', es: '', pt: '' },
  };

  @Watch('showCreateModal')
  onShowCreateModalChanged(newVal: boolean): void {
    if (newVal) {
      this.currentItem = this.getEmptyItem();
      this.expirationDateInput = '';
      this.editingLanguage = 'en';
      this.multilingualData = {
        title: { en: '', es: '', pt: '' },
        excerpt: { en: '', es: '', pt: '' },
        button_title: { en: '', es: '', pt: '' },
      };
    }
  }

  @Watch('showEditModal')
  onShowEditModalChanged(newVal: boolean): void {
    if (newVal && this.currentItem.expiration_date) {
      const date = new Date(this.currentItem.expiration_date);
      this.expirationDateInput = this.formatDateForInput(date);
    } else {
      this.expirationDateInput = '';
    }
  }

  getEmptyItem(): types.CarouselItem {
    return {
      carousel_item_id: 0,
      title: '',
      excerpt: '',
      image_url: '',
      link: '',
      button_title: '',
      expiration_date: undefined,
      status: true,
    };
  }

  isItemArchived(item: types.CarouselItem): boolean {
    // If status is false, it's archived
    if (!item.status) {
      return true;
    }

    // If status is true, check expiration_date
    if (item.expiration_date) {
      const expirationDate = new Date(item.expiration_date);
      const now = new Date();
      // If expiration date has passed, it's archived
      return expirationDate < now;
    }

    // If status is true and no expiration date, it's active
    return false;
  }

  get filteredCarouselItems(): types.CarouselItem[] {
    if (this.currentStatusFilter === 'active') {
      return this.carouselItems.filter((item) => !this.isItemArchived(item));
    }

    if (this.currentStatusFilter === 'archived') {
      return this.carouselItems.filter((item) => this.isItemArchived(item));
    }

    return this.carouselItems;
  }

  parseJsonField(field: string | undefined): { [key: string]: string } {
    if (!field) {
      return { en: '', es: '', pt: '' };
    }
    try {
      const parsed = typeof field === 'string' ? JSON.parse(field) : field;
      return {
        en: parsed.en || '',
        es: parsed.es || '',
        pt: parsed.pt || '',
      };
    } catch (e) {
      // If it's not valid JSON, treat it as a plain string for the current language
      return {
        en: typeof field === 'string' ? field : '',
        es: '',
        pt: '',
      };
    }
  }

  stringifyJsonField(data: { [key: string]: string }): string {
    return JSON.stringify(data);
  }

  getMultilingualText(field: string | undefined, lang: string): string {
    const parsed = this.parseJsonField(field);
    return parsed[lang] || parsed.en || '';
  }

  editItem(item: types.CarouselItem): void {
    this.currentItem = { ...item };
    this.multilingualData = {
      title: this.parseJsonField(item.title),
      excerpt: this.parseJsonField(item.excerpt),
      button_title: this.parseJsonField(item.button_title),
    };
    if (item.expiration_date) {
      const date = new Date(item.expiration_date);
      this.expirationDateInput = this.formatDateForInput(date);
    } else {
      this.expirationDateInput = '';
    }
    this.editingLanguage = 'en';
    this.showEditModal = true;
  }

  confirmDelete(item: types.CarouselItem): void {
    this.itemToDelete = item.carousel_item_id;
    this.showDeleteModal = true;
  }

  deleteItem(): void {
    if (this.itemToDelete !== null) {
      this.$emit('delete-item', this.itemToDelete);
      this.showDeleteModal = false;
      this.itemToDelete = null;
    }
  }

  saveItem(): void {
    // Convert multilingual data to JSON strings
    this.currentItem.title = this.stringifyJsonField(
      this.multilingualData.title,
    );
    this.currentItem.excerpt = this.stringifyJsonField(
      this.multilingualData.excerpt,
    );
    this.currentItem.button_title = this.stringifyJsonField(
      this.multilingualData.button_title,
    );

    if (this.expirationDateInput) {
      this.currentItem.expiration_date = new Date(this.expirationDateInput);
    } else {
      this.currentItem.expiration_date = undefined;
    }

    if (this.showEditModal) {
      this.$emit('update-item', this.currentItem);
    } else {
      this.$emit('create-item', this.currentItem);
    }
    this.closeModal();
  }

  closeModal(): void {
    this.showCreateModal = false;
    this.showEditModal = false;
    this.currentItem = this.getEmptyItem();
    this.expirationDateInput = '';
    this.editingLanguage = 'en';
    this.multilingualData = {
      title: { en: '', es: '', pt: '' },
      excerpt: { en: '', es: '', pt: '' },
      button_title: { en: '', es: '', pt: '' },
    };
  }

  truncateText(text: string, maxLength: number): string {
    if (!text || text.length <= maxLength) {
      return text || '';
    }
    return text.substring(0, maxLength) + '...';
  }

  formatDate(date: Date): string {
    return new Date(date).toLocaleString();
  }

  formatDateForInput(date: Date): string {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  }
}
</script>

<style scoped>
.modal.show {
  display: block;
}
.modal-backdrop.show {
  opacity: 0.5;
}
.nav-tabs .nav-link {
  cursor: pointer;
}
</style>
