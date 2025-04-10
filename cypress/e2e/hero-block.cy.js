describe('App', () => {
  it('Hero block testing', () => {
    cy.visit('/test-page', { 'failOnStatusCode': false })

    // Do following test only for .hero-block which contains heading "Hero block with background image"
    cy.get('.hero-block')
      .filter((index, element) => {
        return Cypress.$(element)
          .find('h2.hero-block__heading')
          .text()
          .includes('Hero block with background image');
      })
      .first()
      .within(() => {
        cy.get('div.hero-block__background').should('exist');
        cy.get('div.hero-block__background > img').should('exist');
        cy.get('div.hero-block__background > img').should('have.attr', 'src').and('include', 'bg-image');

        cy.get('h2.hero-block__heading').should('exist')
        cy.get('h2.hero-block__heading').should('contain.text', 'Hero block with background image')

        cy.get('div.hero-block__main-content').should('exist')
        cy.get('div.hero-block__main-content').should('contain.text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Ipsum nesciunt cum blanditiis ducimus aspernatur. Excepturi incidunt minus aliquam explicabo eaque modi porro, placeat blanditiis. Omnis assumenda in quas eaque officiis.')

        cy.get('.hero-block__link--primary > a').should('exist')
        cy.get('.hero-block__link--primary > a').should('contain.text', 'Learn more')
        cy.get('.hero-block__link--primary > a').should('contain.attr', 'href', 'https://www.silverstripe.co.nz')

        cy.get('.hero-block__link--secondary > a').should('exist')
        cy.get('.hero-block__link--secondary > a').should('contain.text', 'Watch video')
        cy.get('.hero-block__link--secondary > a').should('contain.attr', 'href', 'https://www.youtube.com')
      });
  });

  it('Hero block testing with background color', () => {
    cy.visit('/test-page', { 'failOnStatusCode': false })
    // Do following test only for .hero-block which contains heading "Hero block with background color"
    cy.get('.hero-block')
      .filter((index, element) => {
        console.log("ng-aw ~ .filter ~ element:", element);
        return Cypress.$(element)
          .find('h2.hero-block__heading')
          .text()
          .includes('Hero block with background color');
      })
      .first()
      .within(() => {
        cy.get('div.hero-block__background').should('not.exist');
        cy.get('div.hero-block__background > img').should('not.exist');



        cy.get('h2.hero-block__heading').should('exist')
        cy.get('h2.hero-block__heading').should('contain.text', 'Hero block with background color')

        cy.get('div.hero-block__main-content').should('exist')
        cy.get('div.hero-block__main-content').should('contain.text', 'Consequat eu labore est veniam. Non nulla reprehenderit incididunt elit. Veniam dolore Lorem mollit sit aliqua.')

        cy.get('.hero-block__link--primary > a').should('exist')
        cy.get('.hero-block__link--primary > a').should('contain.text', 'Learn more')
        cy.get('.hero-block__link--primary > a').should('contain.attr', 'href', 'https://www.silverstripe.co.nz')

        cy.get('.hero-block__link--secondary > a').should('exist')
        cy.get('.hero-block__link--secondary > a').should('contain.text', 'Watch video')
        cy.get('.hero-block__link--secondary > a').should('contain.attr', 'href', 'https://www.youtube.com')
      });
  });
});
