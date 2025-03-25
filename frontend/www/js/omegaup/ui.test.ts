import * as ui from './ui';
import * as time from './time';

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
});
