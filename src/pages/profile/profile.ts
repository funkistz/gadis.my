import { Component, ViewChild } from '@angular/core';
import {  NavController, NavParams } from 'ionic-angular';


declare var wordpress_url:string;

@Component({
  selector: 'page-profile',
  templateUrl: 'profile.html'
})
export class ProfilePage {
	@ViewChild('update') Update;
	wordpress_user:string = wordpress_url+'/wp-json/mobiconnector/user';
  	constructor(public navCtrl: NavController,public navParams: NavParams,) {}
  	saveProfile() {
  		this.Update.save();
  	}
}
