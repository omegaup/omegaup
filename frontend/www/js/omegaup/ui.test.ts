import * as ui from './ui';
import * as time from './time';
import notificationsStore from './notificationsStore';

describe('ui', () => {
  describe('formatString', () => {
    it('Should handle strings without replacements', () => {
      expect(ui.formatString('hello', {})).toEqual('hello');
    });

    it('Should handle strings with replacements', () => {
      expect(
        ui.formatString('%(greeting), %(target)!', {
          greeting: 'hello',
          target: 'world',
        }),
      ).toEqual('hello, world!');
    });

    it('Should handle numbers', () => {
      expect(ui.formatString('%(x)', { x: 42 })).toEqual('42');
    });

    it('Should handle dates', () => {
      expect(ui.formatString('%(x!date)', { x: 0 })).toEqual(
        time.formatDate(new Date(0)),
      );
    });

    it('Should handle timestamps', () => {
      expect(ui.formatString('%(x!timestamp)', { x: 0 })).toEqual(
        time.formatDateTime(new Date(0)),
      );
    });

    it('Should handle strings with multiple replacements', () => {
      expect(ui.formatString('%(x) %(x)', { x: 'foo' })).toEqual('foo foo');
    });
  });

  describe('onDismiss callback via ui.* functions', () => {
    // After each test, hide any visible notification to reset the singleton store
    afterEach(() => {
      notificationsStore.commit('hideNotification');
    });

    it('ui.error should pass onDismiss to the store', () => {
      const callback = jest.fn();

      ui.error('Something went wrong', callback);

      expect(notificationsStore.state.onDismiss).toBe(callback);
    });

    it('ui.warning should pass onDismiss to the store', () => {
      const callback = jest.fn();

      ui.warning('This is a warning', callback);

      expect(notificationsStore.state.onDismiss).toBe(callback);
    });

    it('ui.info should pass onDismiss to the store', () => {
      const callback = jest.fn();

      ui.info('Here is some info', callback);

      expect(notificationsStore.state.onDismiss).toBe(callback);
    });

    it('ui.success should pass onDismiss to the store', () => {
      const callback = jest.fn();

      ui.success('Done!', true, callback);

      expect(notificationsStore.state.onDismiss).toBe(callback);
    });

    it('ui.apiError should pass onDismiss to the store', () => {
      const callback = jest.fn();
      // apiError intentionally calls console.error; suppress it for this test
      const consoleSpy = jest
        .spyOn(console, 'error')
        .mockImplementation(() => {});

      ui.apiError({ error: 'Something failed' }, callback);

      consoleSpy.mockRestore();
      expect(notificationsStore.state.onDismiss).toBe(callback);
    });

    it('onDismiss should be called when notification is dismissed', () => {
      const callback = jest.fn();

      ui.error('An error occurred', callback);
      // Simulate user clicking the X button (dismissNotifications action)
      notificationsStore.dispatch('dismissNotifications');

      expect(callback).toHaveBeenCalledTimes(1);
    });

    it('ui.* functions work normally without onDismiss (backward compatible)', () => {
      // No callback passed — should not throw
      expect(() => ui.error('No callback error')).not.toThrow();
      expect(notificationsStore.state.onDismiss).toBeNull();
    });
  });
});
