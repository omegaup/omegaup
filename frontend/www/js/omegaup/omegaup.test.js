'use strict';

var omegaup = require('../dist/omegaup.js');

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

  describe('translations', function() {
    it('Should be loaded correctly', function() {
      expect(typeof omegaup.T.arenaPageTitle).not.toEqual('undefined');
    });
  });
});
