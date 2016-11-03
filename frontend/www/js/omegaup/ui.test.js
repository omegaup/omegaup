'use strict';
require('./ui.js');

describe('omegaup.ui', function() {
  describe('formatString', function() {
    it('Should handle strings without replacements', function() {
      expect(omegaup.UI.formatString('hello', {})).toEqual('hello');
    });

    it('Should handle strings with replacements', function() {
      expect(omegaup.UI.formatString('%(greeting), %(target)!',
                                     {greeting: 'hello', target: 'world'}))
          .toEqual('hello, world!');
    });

    it('Should handle numbers', function() {
      expect(omegaup.UI.formatString('%(x)', {x: 42})).toEqual('42');
    });

    it('Should handle strings with multiple replacements', function() {
      expect(omegaup.UI.formatString('%(x) %(x)', {x: 'foo'}))
          .toEqual('foo foo');
    });
  });
});
