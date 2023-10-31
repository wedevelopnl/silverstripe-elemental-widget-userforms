describe('WeDevelop', () => {
  describe('SilverstripeElementalWidgetUserforms', () => {
    beforeEach(() => {
      cy.reloadDatabase();
      cy.login();
    });

    it('can create a user form widget', () => {
      cy.intercept({
        url: '/admin/widgets/wedevelop-elementalwidget-userform-widget-userformwidget/EditForm/field/wedevelop-elementalwidget-userform-widget-userformwidget/schema/SearchForm/',
      }).as('searchForm');

      cy.visit('/admin/widgets');
      cy.wait('@searchForm');

      cy.get('.btn-toolbar')
        .contains('Add User form widget')
        .click();

      cy.get('input[name="Title"]')
        .type('Cypress');
      cy.get('input[name="IsPartOfCollection"]')
        .uncheck();
      cy.get('button[name="action_doSaveAndClose"]')
        .click();
      cy.wait('@searchForm');

      cy.get('#Form_EditForm_WeDevelop-ElementalWidget-UserForm-Widget-UserFormWidget-nc')
        .find('tr[data-class$="UserFormWidget"]')
        .first()
        .within(() => {
          cy.get('td.col-Title')
            .text()
            .should('eq', 'Cypress');
        })
        .click();

      cy.get('input[name="IsPartOfCollection"]')
        .check();
      cy.get('button[name="action_doSaveAndClose"]')
        .click();
      cy.wait('@searchForm');

      cy.get('#Form_EditForm_wedevelop-elementalwidget-userform-widget-userformwidget')
        .find('tr[data-class$="UserFormWidget"]')
        .first()
        .within(() => {
          cy.get('td.col-Title')
            .text()
            .should('eq', 'Cypress');
        })
    });
  });
});