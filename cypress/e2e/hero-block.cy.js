const HeroBlock = {
  main: '.hero-block',
  heading: 'h2.hero-block__heading',
  background: 'div.hero-block__background',
  mainContent: 'div.hero-block__main-content',
  primaryLink: '.hero-block__link--primary > a',
  secondaryLink: '.hero-block__link--secondary > a',
  backgroundImage: 'div.hero-block__background > img',
  mainContentRight: 'div.hero-block__content--right',
  mainContentLeft: 'div.hero-block__content--left',
};

describe('App', () => {
  it('Hero block testing', () => {
    cy.visit('/test-page', { 'failOnStatusCode': false })

    // Do following test only for .hero-block which contains heading "Hero block with background image"
    cy.get(HeroBlock.main)
      .filter((index, element) => {
        return Cypress.$(element)
          .find(HeroBlock.heading)
          .text()
          .includes('Hero block with background image');
      })
      .first()
      .within(() => {
        cy.get(HeroBlock.background).should('exist');
        cy.get(HeroBlock.backgroundImage).should('exist');
        cy.get(HeroBlock.backgroundImage).should('have.attr', 'src').and('include', 'bg-image');

        cy.get(HeroBlock.heading).should('exist')
        cy.get(HeroBlock.heading).should('contain.text', 'Hero block with background image')

        cy.get(HeroBlock.mainContent).should('exist')
        cy.get(HeroBlock.mainContent).should('contain.text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Ipsum nesciunt cum blanditiis ducimus aspernatur. Excepturi incidunt minus aliquam explicabo eaque modi porro, placeat blanditiis. Omnis assumenda in quas eaque officiis.')

        cy.get(HeroBlock.primaryLink).should('exist')
        cy.get(HeroBlock.primaryLink).should('contain.text', 'Learn more')
        cy.get(HeroBlock.primaryLink).should('contain.attr', 'href', 'https://www.silverstripe.co.nz')

        cy.get(HeroBlock.secondaryLink).should('exist')
        cy.get(HeroBlock.secondaryLink).should('contain.text', 'Watch video')
        cy.get(HeroBlock.secondaryLink).should('contain.attr', 'href', 'https://www.youtube.com')
      });
  });

  it('Hero block testing right aligned', () => {
    cy.visit('/test-page', { 'failOnStatusCode': false })

    // Do following test only for .hero-block which contains heading "Hero block with right aligned text"
    cy.get(HeroBlock.main)
      .filter((index, element) => {
        return Cypress.$(element)
          .find(HeroBlock.heading)
          .text()
          .includes('Hero block with right aligned text');
      })
      .first()
      .within(() => {
        cy.get(HeroBlock.background).should('exist');
        cy.get(HeroBlock.backgroundImage).should('exist');
        cy.get(HeroBlock.backgroundImage).should('have.attr', 'src').and('include', 'bg-image');

        cy.get(HeroBlock.mainContentRight).should('exist');

        cy.get(HeroBlock.heading).should('exist')
        cy.get(HeroBlock.heading).should('contain.text', 'Hero block with right aligned text')

        cy.get(HeroBlock.mainContent).should('exist')
        cy.get(HeroBlock.mainContent).should('contain.text', 'Proident Lorem ex laborum consectetur irure magna culpa minim enim occaecat irure cupidatat tempor aliqua. Aute non veniam ex amet aute sit officia eu pariatur amet ad. Sint ex est pariatur proident aute nostrud ea sint aute. Occaecat in minim veniam officia fugiat aliqua eu voluptate velit eiusmod duis incididunt.')

        cy.get(HeroBlock.primaryLink).should('exist')
        cy.get(HeroBlock.primaryLink).should('contain.text', 'Learn more')
        cy.get(HeroBlock.primaryLink).should('contain.attr', 'href', 'https://www.silverstripe.co.nz')

        cy.get(HeroBlock.secondaryLink).should('exist')
        cy.get(HeroBlock.secondaryLink).should('contain.text', 'Watch video')
        cy.get(HeroBlock.secondaryLink).should('contain.attr', 'href', 'https://www.youtube.com')
      });
  });

  it('Hero block testing left aligned', () => {
    cy.visit('/test-page', { 'failOnStatusCode': false })

    // Do following test only for .hero-block which contains heading "Hero block with left aligned text"
    cy.get(HeroBlock.main)
      .filter((index, element) => {
        return Cypress.$(element)
          .find(HeroBlock.heading)
          .text()
          .includes('Hero block with left aligned text');
      })
      .first()
      .within(() => {
        cy.get(HeroBlock.background).should('exist');
        cy.get(HeroBlock.backgroundImage).should('exist');
        cy.get(HeroBlock.backgroundImage).should('have.attr', 'src').and('include', 'bg-image');

        cy.get(HeroBlock.mainContentLeft).should('exist');

        cy.get(HeroBlock.heading).should('exist')
        cy.get(HeroBlock.heading).should('contain.text', 'Hero block with left aligned text')

        cy.get(HeroBlock.mainContent).should('exist')
        cy.get(HeroBlock.mainContent).should('contain.text', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Ipsum nesciunt cum blanditiis ducimus aspernatur. Excepturi incidunt minus aliquam explicabo eaque modi porro, placeat blanditiis. Omnis assumenda in quas eaque officiis.')

        cy.get(HeroBlock.primaryLink).should('exist')
        cy.get(HeroBlock.primaryLink).should('contain.text', 'Learn more')
        cy.get(HeroBlock.primaryLink).should('contain.attr', 'href', 'https://www.silverstripe.co.nz')

        cy.get(HeroBlock.secondaryLink).should('exist')
        cy.get(HeroBlock.secondaryLink).should('contain.text', 'Watch video')
        cy.get(HeroBlock.secondaryLink).should('contain.attr', 'href', 'https://www.youtube.com')
      });
  });


  it('Hero block testing with background color', () => {
    cy.visit('/test-page', { 'failOnStatusCode': false })
    // Do following test only for .hero-block which contains heading "Hero block with background color"
    cy.get(HeroBlock.main)
      .filter((index, element) => {
        return Cypress.$(element)
          .find(HeroBlock.heading)
          .text()
          .includes('Hero block with background color');
      })
      .first()
      .within((block) => {
        cy.get(block).should('have.css', 'background-color')
          .and('eq', 'rgb(0, 0, 0)')

        cy.get(HeroBlock.background).should('not.exist');
        cy.get(HeroBlock.backgroundImage).should('not.exist');

        cy.get(HeroBlock.heading).should('exist')
        cy.get(HeroBlock.heading).should('contain.text', 'Hero block with background color')
        cy.get(HeroBlock.heading).should('have.css', 'color')
          .and('eq', 'rgb(255, 255, 255)')

        cy.get(HeroBlock.mainContent).should('exist')
        cy.get(HeroBlock.mainContent).should('contain.text', 'Consequat eu labore est veniam. Non nulla reprehenderit incididunt elit. Veniam dolore Lorem mollit sit aliqua.')

        cy.get(HeroBlock.primaryLink).should('exist')
        cy.get(HeroBlock.primaryLink).should('contain.text', 'Learn more')
        cy.get(HeroBlock.primaryLink).should('contain.attr', 'href', 'https://www.silverstripe.co.nz')

        cy.get(HeroBlock.secondaryLink).should('exist')
        cy.get(HeroBlock.secondaryLink).should('contain.text', 'Watch video')
        cy.get(HeroBlock.secondaryLink).should('contain.attr', 'href', 'https://www.youtube.com')
      });
  });
});
