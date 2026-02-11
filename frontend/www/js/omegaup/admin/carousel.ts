import admin_Carousel from '../components/admin/Carousel.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CarouselManagementPayload();

  const adminCarousel = new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-carousel': admin_Carousel,
    },
    data: () => ({
      carouselItems: payload.carouselItems,
    }),
    render: function (createElement) {
      return createElement('omegaup-admin-carousel', {
        props: {
          carouselItems: this.carouselItems,
        },
        on: {
          'create-item': (item: types.CarouselItem): void => {
            api.CarouselItems.create({
              title: item.title,
              excerpt: item.excerpt,
              image_url: item.image_url,
              link: item.link,
              buttonTitle: item.button_title,
              expiration_date: item.expiration_date
                ? item.expiration_date.toISOString()
                : null,
              status: item.status,
            })
              .then(() => {
                ui.success(T.carouselItemCreated);
                adminCarousel.carouselItems = [...adminCarousel.carouselItems];
                return api.CarouselItems.list({});
              })
              .then((response) => {
                adminCarousel.carouselItems = response.carouselItems;
              })
              .catch(ui.apiError);
          },
          'update-item': (item: types.CarouselItem): void => {
            api.CarouselItems.update({
              carousel_item_id: item.carousel_item_id,
              title: item.title,
              excerpt: item.excerpt,
              image_url: item.image_url,
              link: item.link,
              buttonTitle: item.button_title,
              expiration_date: item.expiration_date
                ? item.expiration_date.toISOString()
                : null,
              status: item.status,
            })
              .then(() => {
                ui.success(T.carouselItemUpdated);
                return api.CarouselItems.list({});
              })
              .then((response) => {
                adminCarousel.carouselItems = response.carouselItems;
              })
              .catch(ui.apiError);
          },
          'delete-item': (carouselItemId: number): void => {
            api.CarouselItems.delete({ carousel_item_id: carouselItemId })
              .then(() => {
                ui.success(T.carouselItemDeleted);
                return api.CarouselItems.list({});
              })
              .then((response) => {
                adminCarousel.carouselItems = response.carouselItems;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
