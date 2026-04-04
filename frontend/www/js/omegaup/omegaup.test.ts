import { Experiments } from './omegaup';
import T from './lang';

describe('omegaup', () => {
  describe('experiments', () => {
    it('Should handle unknown experiments', () => {
      const experiments = new Experiments([]);
      expect(experiments.isEnabled('foo')).toEqual(false);
    });

    it('Should handle known experiments', () => {
      const experiments = new Experiments(['foo']);
      expect(experiments.isEnabled('foo')).toEqual(true);
    });
  });

  describe('translations', () => {
    it('Should be loaded correctly', () => {
      expect(typeof T.arenaPageTitle).not.toEqual('undefined');
    });
  });
});
