import { Component } from '@angular/core';

/**
 * Generated class for the CrossSellComponent component.
 *
 * See https://angular.io/api/core/Component for more info on Angular
 * Components.
 */
@Component({
  selector: 'cross-sell',
  templateUrl: 'cross-sell.html'
})
export class CrossSellComponent {

  text: string;

  constructor() {
    console.log('Hello CrossSellComponent Component');
    this.text = 'Hello World';
  }

}
