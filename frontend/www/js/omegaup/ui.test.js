'use strict';

require('../dist/commons.js');
var omegaup = require('../dist/omegaup.js');

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

  describe('parseDuration', function() {
    it('Should handle valid inputs', function() {
      expect(omegaup.UI.parseDuration('0')).toEqual(0);
      expect(omegaup.UI.parseDuration('1')).toEqual(1000.0);
      expect(omegaup.UI.parseDuration('1s')).toEqual(1000.0);
      expect(omegaup.UI.parseDuration('1ms')).toEqual(1.0);
      expect(omegaup.UI.parseDuration('1.0ms')).toEqual(1.0);
      expect(omegaup.UI.parseDuration('0.001s')).toEqual(1.0);
      expect(omegaup.UI.parseDuration('1m30s')).toEqual(90000.0);
    });

    it('Should reject invalid inputs', function() {
      expect(omegaup.UI.parseDuration('-1s')).toBe(null);
      expect(omegaup.UI.parseDuration('.s')).toBe(null);
    });
  });
});
