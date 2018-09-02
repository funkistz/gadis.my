import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
// Page
import { ContactPage } from '../contact/contact';


@Component({
  selector: 'page-about',
  templateUrl: 'about.html',
})
export class AboutPage {

  	ContactPage = ContactPage;
	faded: boolean = false;
	constructor(private navCtrl: NavController) {
		setTimeout(() => {
				this.faded = true;
		},100);
	}
	gotoContact(){
		if(this.navCtrl.getPrevious().component == this.ContactPage) this.navCtrl.pop();
		else this.navCtrl.push(this.ContactPage);
	}

}
