import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { TabsPage } from '../../pages/tabs/tabs';
import { Slides } from 'ionic-angular';
import { Storage } from '@ionic/storage';

@Component({
	selector: 'page-onboarding',
	templateUrl: 'onboarding.html',
})
export class OnboardingPage {

	@ViewChild('slider') slider: Slides;

	onboard = false;

	slides = [
		{
			title: "Welcome to Gadis.my",
			image: "assets/images/onboarding/1.jpg",
		},
		{
			title: "Shop Till You Drop",
			image: "assets/images/onboarding/2.jpg",
		},
		{
			title: "Sell Your New and Preloved Item",
			image: "assets/images/onboarding/5.jpg",
		}
	];

	final = {
		title: "Quick Secure Reliable",
		image: "assets/images/onboarding/4.jpg",
	};

	faded: boolean = false;
	constructor(
		public navCtrl: NavController,
		public storage: Storage,
	) {
		setTimeout(() => {
			this.faded = true;
		}, 100);
	}

	ionViewDidEnter() {
		this.storage.get('onboarding').then((val) => {

			if (val) {
				this.navCtrl.setRoot(TabsPage);
			} else {
				this.onboard = true;
			}
		});
	}

	startNow() {
		this.storage.set('onboarding', true);
		this.navCtrl.setRoot(TabsPage);
	}

	next() {
		this.slider.slideNext();
	}

}
