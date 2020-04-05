'use strict';

require('../dist/commons.js');
var omegaup = require('../dist/omegaup.js');

describe('omegaup.UI', function() {
  describe('formatString', function() {
    it('Should handle strings without replacements', function() {
      expect(omegaup.UI.formatString('hello', {})).toEqual('hello');
    });

    it('Should handle strings with replacements', function() {
      expect(
        omegaup.UI.formatString('%(greeting), %(target)!', {
          greeting: 'hello',
          target: 'world',
        }),
      ).toEqual('hello, world!');
    });

    it('Should handle numbers', function() {
      expect(omegaup.UI.formatString('%(x)', { x: 42 })).toEqual('42');
    });

    it('Should handle dates', function() {
      expect(omegaup.UI.formatString('%(x!date)', { x: 0 })).toEqual(
        omegaup.Time.formatDate(new Date(0)),
      );
    });

    it('Should handle timestamps', function() {
      expect(omegaup.UI.formatString('%(x!timestamp)', { x: 0 })).toEqual(
        omegaup.Time.formatDateTime(new Date(0)),
      );
    });

    it('Should handle strings with multiple replacements', function() {
      expect(omegaup.UI.formatString('%(x) %(x)', { x: 'foo' })).toEqual(
        'foo foo',
      );
    });
  });
});
