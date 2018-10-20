import { Component, Input } from '@angular/core';

/**
 * Generated class for the GLoaderComponent component.
 *
 * See https://angular.io/api/core/Component for more info on Angular
 * Components.
 */
@Component({
  selector: 'g-loader',
  templateUrl: 'g-loader.html'
})
export class GLoaderComponent {

  @Input() gstyle: string;
  text: string;

  constructor() {

  }

}
