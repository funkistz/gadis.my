import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';

@Component({
  selector: 'page-privacy',
  templateUrl: 'privacy.html',
})
export class PrivacyPage {
	faded: boolean = false;
	constructor() {
		setTimeout(() => {
				this.faded = true;
		},100);
	}

}
