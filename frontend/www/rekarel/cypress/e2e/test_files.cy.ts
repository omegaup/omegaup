import type {GetKarelController } from "../../src/index"

const test_world = '<ejecucion version="1.1">\n\t<condiciones instruccionesMaximasAEjecutar="10000000" longitudStack="65000" memoriaStack="65000" llamadaMaxima="5"/>\n\t<mundos>\n\t\t<mundo nombre="mundo_0" ancho="100" alto="100">\n\t\t\t<monton x="4" y="3" zumbadores="2"/>\n\t\t\t<monton x="7" y="3" zumbadores="2"/>\n\t\t\t<monton x="1" y="6" zumbadores="1"/>\n\t\t\t<monton x="5" y="6" zumbadores="1"/>\n\t\t\t<posicionDump x="4" y="1"/>\n\t\t\t<posicionDump x="6" y="1"/>\n\t\t\t<posicionDump x="8" y="1"/>\n\t\t</mundo>\n\t</mundos>\n\t<programas tipoEjecucion="CONTINUA" intruccionesCambioContexto="1" milisegundosParaPasoAutomatico="0">\n\t\t<programa nombre="p1" ruta="{$2$}" mundoDeEjecucion="mundo_0" xKarel="1" yKarel="1" direccionKarel="NORTE" mochilaKarel="4">\n\t\t\t<despliega tipo="AVANZA"/>\n\t\t\t<despliega tipo="COGE_ZUMBADOR"/>\n\t\t\t<despliega tipo="DEJA_ZUMBADOR"/>\n\t\t\t<despliega tipo="GIRA_IZQUIERDA"/>\n\t\t\t<despliega tipo="MOCHILA"/>\n\t\t\t<despliega tipo="MUNDO"/>\n\t\t\t<despliega tipo="ORIENTACION"/>\n\t\t\t<despliega tipo="POSICION"/>\n\t\t\t<despliega tipo="UNIVERSO"/>\n\t\t</programa>\n\t</programas>\n</ejecucion>\n';

describe('Test world file format', () => {
  /* ==== Test Created with Cypress Studio ==== */
  it('test_file_save', function() {
    /* ==== Generated with Cypress Studio ==== */
    cy.visit('http://localhost:5500/webapp/');
    cy.wait(500);
    cy.get('body').type(
        '{rightarrow}{rightarrow}{rightarrow}z{rightarrow}{rightarrow}z{rightarrow}'+
        '{rightarrow}z{leftarrow}{uparrow}{uparrow}ee{leftarrow}{leftarrow}{leftarrow}2'+
        '{uparrow}{leftarrow}{uparrow}{leftarrow}{uparrow}{leftarrow}e'+
        '{rightarrow}{rightarrow}{rightarrow}{rightarrow}1'
    );
    cy.wait(500);
    cy.get('#beeperBag').clear();
    cy.get('#beeperBag').type('04');
    cy.get('.container-md > .btn-toolbar').click();
    cy.get('#evaluatorBtnDesktop > .d-none').click();
    cy.wait(500);
    cy.get('#evaluatePosition').check();
    cy.get('#evaluateOrientation').check();
    cy.get('#evaluateBag').check();
    cy.get('#evaluateUniverse').check();
    cy.get('#countMoves').check();
    cy.get('#countTurns').check();
    cy.get('#countPicks').check();
    cy.get('#countPuts').check();
    cy.get('#evaluatorForm > .modal-dialog > .modal-content > .modal-footer > .btn-primary').click();
    cy.get('#navbarDropdownMenuLink').click();
    cy.get('.navbar-nav > :nth-child(1) > .dropdown-menu > :nth-child(6) > .dropdown-item').click();

    cy.get('#worldDataIn').should(
      'have.value',
      test_world
    );

    /* ==== End Cypress Studio ==== */
  });

  it('Test open world from text', function() {
    cy.visit('http://localhost:5500/webapp/');
    cy.get('#navbarDropdownMenuLink').click();
    cy.get('#openWorldInBtn').click();
    cy.wait(500);
    cy.get('#inputWorldField').click();
    cy.get('#inputWorldField').type(test_world, {parseSpecialCharSequences:false, delay:0});
    cy.get('#openWorldTextBtn').click();
    cy.wait(100);
    cy.window().then((win) => {
          
        const controller = ((win as any).karel.GetKarelController as typeof GetKarelController)();
        const world = controller.world;
        
        expect(world.save("start")).to.equal(test_world);
        
    });
    cy.get('#beeperBag').should('have.value', '4');
    cy.get('#evaluatorBtnDesktop > .d-none').click();
    cy.get('#evaluatePosition').should('have.value', 'on');
    cy.get('#evaluateOrientation').should('have.value', 'on');
    cy.get('#evaluateBag').should('have.value', 'on');
    cy.get('#evaluateUniverse').should('have.value', 'on');
    cy.get('#countMoves').should('have.value', 'on');
    cy.get('#countTurns').should('have.value', 'on');
    cy.get('#countPicks').should('have.value', 'on');
    cy.get('#countPuts').should('have.value', 'on');    
  });

  /* ==== Test Created with Cypress Studio ==== */
  it('Test open from URI one', function() {
    /* ==== Generated with Cypress Studio ==== */
    cy.visit('http://127.0.0.1:5500/webapp/#w=S1cAGoCWmADo/QAA6P0AAAUAAAD/////////////////////Y2MAAAASAAIACgQAAQwAGWgAAm8AAswAAtMAAzABCjIBCTQBATYBApgBApoBAvwBCv4BAmECEsUCGioDBAAAAA4AAAAOAA==');
    cy.get('#beeperBag').should('have.value', '14');
    cy.get('#evaluatorBtnDesktop').click();
    cy.get('#evaluateUniverse').should('have.value', 'on');
    cy.get('#evaluateUniverse').should('be.checked');
    cy.get('#evaluateBag').should('not.be.checked');
    cy.get('#evaluateOrientation').should('not.be.checked');
    cy.get('#evaluatePosition').should('not.be.checked');
    cy.get('#countMoves').should('not.be.checked');
    cy.get('#countTurns').should('not.be.checked');
    cy.get('#countPicks').should('not.be.checked');
    cy.get('#countPuts').should('not.be.checked');
    cy.get('#evaluatorForm > .modal-dialog > .modal-content > .modal-header > .btn-close').click();
    cy.get('#infiniteBeepersBtn').should('have.class', 'btn-body');
    cy.get('#beeperBag').should('be.visible');
    /* ==== End Cypress Studio ==== */
  });

  /* ==== Test Created with Cypress Studio ==== */
  it('World from URI infinite bag', function() {
    /* ==== Generated with Cypress Studio ==== */
    cy.visit('http://127.0.0.1:5500/webapp/#w=S1cBKoCWmADo/QAA6P0AAAUAAAD/////////////////////Y2MAAAAAAAD/////AQA=');
    cy.get('#infiniteBeepersBtn').should('have.class', 'btn-info');
    cy.get('#beeperBag').should('not.be.visible');
    /* ==== End Cypress Studio ==== */
  });
});
  