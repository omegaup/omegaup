import { createLocalVue, shallowMount, mount } from '@vue/test-utils';
import BootstrapVue, { IconsPlugin, BButton } from 'bootstrap-vue';
import Vue from 'vue';
import DeleteConfirmationForm from './DeleteConfirmationForm.vue';
import T from '../../../../lang';
import * as ui from '@/js/omegaup/ui';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('DeleteConfirmationForm.vue', () => {

  it('Should set commitMessage when visible changes', async () => {
    const wrapper = shallowMount(DeleteConfirmationForm, {
      localVue,
      propsData: {
        visible: false,
        itemName: 'My Item',
        itemId: '42',
        onCancel: () => {},
      },
      provide: { problemAlias: 'alias' },
    });

    expect((wrapper.vm as any).commitMessage).toBe('');

    wrapper.setProps({ visible: true });
    await Vue.nextTick();

    expect((wrapper.vm as any).commitMessage).toBe(
      `${T.problemEditDeletingPrefix} My Item`,
    );

    const messageHidden = wrapper.find('input[type="hidden"][name="message"]');
    expect(messageHidden.exists()).toBeTruthy();
    expect((messageHidden.element as HTMLInputElement).value).toBe(
      `${T.problemEditDeletingPrefix} My Item`,
    );
  });

  it('Should display correct button labels', async () => {
    const wrapper = mount(DeleteConfirmationForm, {
      localVue,
      propsData: {
        visible: true,
        itemName: 'Item',
        itemId: '1',
        onCancel: jest.fn(),
      },
      provide: { problemAlias: 'alias' },
    });

    const submitBtn = wrapper.find('button.btn-danger');
    const cancelBtn = wrapper.find('button.btn-secondary');

    expect(submitBtn.text()).toBe(T.problemEditConfirmDeletion);
    expect(cancelBtn.text()).toBe(T.wordsCancel);
  });

  it('Should include proper hidden fields (request, alias, contents)', async () => {
    const wrapper = shallowMount(DeleteConfirmationForm, {
      localVue,
      propsData: {
        visible: true,
        itemName: 'Deleted Item',
        itemId: '1234',
        onCancel: () => {},
      },
      provide: { problemAlias: 'the-alias' },
    });

    await Vue.nextTick();

    const requestHidden = wrapper.find('input[type="hidden"][name="request"]');
    expect(requestHidden.exists()).toBeTruthy();
    expect((requestHidden.element as HTMLInputElement).value).toBe('deleteGroupCase');

    const aliasHidden = wrapper.find('input[type="hidden"][name="problem_alias"]');
    expect(aliasHidden.exists()).toBeTruthy();
    expect((aliasHidden.element as HTMLInputElement).value).toBe('the-alias');

    const contentsHidden = wrapper.find('input[type="hidden"][name="contents"]');
    expect(contentsHidden.exists()).toBeTruthy();
    const parsed = JSON.parse((contentsHidden.element as HTMLInputElement).value);
    expect(parsed.id).toBe('1234');
  });

  it('Should prevent submit and show error when commitMessage empty', async () => {
    const errorSpy = jest.spyOn(ui, 'error').mockImplementation(() => {});
    const wrapper = mount(DeleteConfirmationForm, {
      localVue,
      propsData: {
        visible: true,
        itemName: 'to delete',
        itemId: '999',
        onCancel: () => {},
      },
      provide: { problemAlias: 'alias' },
    });

    wrapper.setData({ commitMessage: '      ' });
    await Vue.nextTick();

    const form = wrapper.find('form');
    await form.trigger('submit');

    expect(errorSpy).toHaveBeenCalledWith(T.editFieldRequired);

    errorSpy.mockRestore();
  });

  it('Should call onCancel and clear commitMessage when cancel clicked', async () => {
    const onCancel = jest.fn();
    
    const wrapper = mount(DeleteConfirmationForm, {
      localVue,
      propsData: {
        visible: false,
        itemName: 'to delete',
        itemId: '1',
        onCancel,
      },
      provide: { problemAlias: 'alias' },
    });

    wrapper.setProps({ visible: true });
    await Vue.nextTick();
    
    expect((wrapper.vm as any).commitMessage).toBe(
      `${T.problemEditDeletingPrefix} to delete`,
    );

    const cancelBtn = wrapper.find('button.btn-secondary');
    await cancelBtn.trigger('click');

    expect(onCancel).toHaveBeenCalled();
    expect((wrapper.vm as any).commitMessage).toBe('');
  });

  it('Should disable submit button when commitMessage is empty', async () => {
    const wrapper = mount(DeleteConfirmationForm, {
      localVue,
      propsData: {
        visible: true,
        itemName: 'Item',
        itemId: '1',
        onCancel: jest.fn(),
      },
      provide: { problemAlias: 'alias' },
    });

    wrapper.setData({ commitMessage: '' });
    await Vue.nextTick();

    const submitBtn = wrapper.find('button.btn-danger');
    expect(submitBtn.attributes('disabled')).toBe('disabled');
    
    wrapper.setData({ commitMessage: 'Delete message' });
    await Vue.nextTick();
    
    expect(submitBtn.attributes('disabled')).toBeUndefined();
  });
});
