'use strict';
require('./omegaup.js');

describe('omegaup', function() {
  describe('experiments', function() {
    it('Should handle unknown experiments', function() {
      var experiments = new omegaup.Experiments();
      expect(experiments.isEnabled('foo')).toEqual(false);
    });

    it('Should handle known experiments', function() {
      var experiments = new omegaup.Experiments(['foo']);
      expect(experiments.isEnabled('foo')).toEqual(true);
    });
  });
});

