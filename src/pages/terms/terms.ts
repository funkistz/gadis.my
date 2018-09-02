import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';

@Component({
  selector: 'page-terms',
  templateUrl: 'terms.html',
})
export class TermsPage {

  	faded: boolean = false;
	constructor() {
		setTimeout(() => {
				this.faded = true;
		},100);
	}

}
