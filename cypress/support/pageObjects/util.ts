const getEditorIframeBody = () => {
  return cy
    .get('iframe')
    .its('0.contentDocument.body')
    .should((body) => {
      expect(Cypress.$(body).has('.view-line').length).gt(0);
    })
    .then(cy.wrap);
};
export default getEditorIframeBody;
