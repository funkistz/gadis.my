import { Component, NgZone, ViewChild } from '@angular/core';
import { NavController, NavParams, Platform } from 'ionic-angular';
import { Http, Headers } from '@angular/http';

// Page
import { LoginPage } from '../login/login';
import { CheckoutPage } from '../checkout/checkout';

declare var wordpress_url;
declare var display_mode;

@Component({
  selector: 'page-address',
  templateUrl: 'address.html'
})
export class AddressPage {
	@ViewChild('location') Location;
	LoginPage = LoginPage;
	CheckoutPage = CheckoutPage;
	constructor(
		public navCtrl: NavController,
		public navParams: NavParams,
		public http: Http,
	    public ngZone: NgZone
	) {}
	getLocation() {
		this.Location.location();
	}
}
