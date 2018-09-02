import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';

// Page
import { LoginPage } from '../login/login';

declare var wordpress_url;
declare var display_mode;


@Component({
  selector: 'page-signup',
  templateUrl: 'signup.html'
})
export class SignupPage {

	LoginPage = LoginPage;

	constructor(
		public navCtrl: NavController,
		public navParams: NavParams
	) {}
}
