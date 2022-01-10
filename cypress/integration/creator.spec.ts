import { CaseOptions } from '../support/types';
import { StoreState } from '@/js/omegaup/problem/creator/types';
import { Store } from 'vuex';
import { NIL } from 'uuid';

let store: Store<StoreState> | null = null;

describe('Basic Commands Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.login({ username: 'user', password: 'user' });
    cy.visit('/problem/creator');

    // Expose store
    cy.window().should('have.property', 'creator');
    cy.window().then((window) => {
      store = (window as any).creator.$store;
    });
  });

  it('Should add multiple ungrouped case to the store', async () => {
    const caseOptions: CaseOptions = {
      caseName: 'case1',
      groupName: NIL,
      points: 20,
      autoPoints: false,
    };

    cy.addCase(caseOptions);

    // Since all cy commands are async, we need to add a dummy wait to ensure this expect will be executed after the addCase command
    cy.wait(1).then(() => {
      const groups = store?.state.casesStore.groups;
      expect(groups?.length).to.equal(1);
      expect(groups?.[0].name).to.equal(caseOptions.caseName);
      expect(groups?.[0].points).to.equal(caseOptions.points);
      expect(groups?.[0].ungroupedCase).to.equal(true);
    });

    const caseOptions2: CaseOptions = {
      caseName: 'case2',
      groupName: NIL,
      points: null,
      autoPoints: true,
    };

    cy.addCase(caseOptions2);
    cy.wait(1).then(() => {
      const groups = store?.state.casesStore.groups;
      expect(groups?.length).to.equal(2);
      expect(groups?.[1].name).to.equal(caseOptions2.caseName);
      expect(groups?.[1].ungroupedCase).to.equal(true);

      // Assert points
      expect(groups?.[0].points).to.equal(20);
      expect(groups?.[1].points).to.equal(80);
    });

    const caseOptions3: CaseOptions = {
      caseName: 'case3',
      groupName: NIL,
      points: null,
      autoPoints: true,
    };

    cy.addCase(caseOptions3);
    cy.wait(1).then(() => {
      const groups = store?.state.casesStore.groups;
      expect(groups?.length).to.equal(3);
      expect(groups?.[2].name).to.equal(caseOptions3.caseName);
      expect(groups?.[2].ungroupedCase).to.equal(true);

      // Assert points
      expect(groups?.[0].points).to.equal(20);
      expect(groups?.[1].points).to.equal(40);
      expect(groups?.[2].points).to.equal(40);
    });
  });
});
