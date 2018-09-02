import { Component } from '@angular/core';
import { NavController, NavParams, Platform} from 'ionic-angular';
// Page
import { OrderPage } from '../order/order';
import { Storage } from '@ionic/storage';

@Component({
  selector: 'page-thanks',
  templateUrl: 'thanks.html',
})
export class ThanksPage {
	OrderPage = OrderPage;
	params: Object;
	id:string; 
	isLogin:boolean;
	
	constructor(
		navParams: NavParams,
		storage: Storage,
		public navCtrl: NavController,
		public platform: Platform
		) {
		this.params = navParams.get('params');
		console.log(this.params);
	}
	ngOnInit() {
        this.platform.ready().then(() => {
            this.platform.registerBackButtonAction(() => {
                this.navCtrl.popToRoot();
            });
        })
    }
}
