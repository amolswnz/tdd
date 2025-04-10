describe('App', () => {
  it('Hero block testing', () => {
    cy.visit('/test-page', { 'failOnStatusCode': false })

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
