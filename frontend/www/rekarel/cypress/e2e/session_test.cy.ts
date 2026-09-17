import { World } from "@rekarel/core";
import type {GetKarelController } from "../../src/index"
describe('Test session', () => {
    it('Test world save', () => {
      cy.visit('http://localhost:5500/webapp/');
      cy.wait(500);

      cy.get('#worldCanvas').focus();
  
      cy.get('body').type('w1{rightarrow}2a{rightarrow}g');
  
      cy.get('#desktopCompileKarel').click();
  
      cy.reload();  
      cy.wait(500);
  
      cy.window().then((win) => {
        const controller = (win.karel.GetKarelController as typeof GetKarelController)();
        const world = controller.world;
        const wallsValue = world.walls(1, 1);
        const karelPos = {i:world.start_i, j:world.start_j};
        const orientation = world.startOrientation;
        expect(wallsValue).to.equal(15);
        expect(karelPos.i).to.equal(1)
        expect(karelPos.j).to.equal(3)
        expect(orientation).to.equal(0)
      });
    });
  });
  