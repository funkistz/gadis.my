import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { Http, Headers } from '@angular/http';
import { Observable } from 'rxjs/Observable';
// Custom
import { Core } from '../../service/core.service';
import { Storage } from '@ionic/storage';
import { Toast } from '@ionic-native/toast';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';

// Page
import { DetailOrderPage } from '../detail-order/detail-order';
declare var wordpress_url: string;
declare var date_format: string;
declare var wordpress_per_page: Number;
const wordpress_order = wordpress_url + '/wp-json/wooconnector/order';

@Component({
	selector: 'page-order',
	templateUrl: 'order.html',
	providers: [Core]
})
export class OrderPage {
	DetailOrderPage = DetailOrderPage;
	login: Object = {}; data: Object[]; date_format: string = date_format;
	page = 1; over: boolean;
	noOrder: boolean = false;
	customer: any;

	constructor(
		public navCtrl: NavController,
		public navParams: NavParams,
		private http: Http,
		private core: Core,
		private storage: Storage,
		private Toast: Toast,
		public WP: WoocommerceProvider
	) {

	}
	ionViewDidEnter() {
		this.page = 1;
		this.storage.get('customer').then(val => {
			console.log(val);
			if (val && val['id']) {
				this.customer = val;

				this.getData(val['id']).subscribe(order => {
					if (order.length > 0) {
						this.noOrder = false;
						this.page++;
						this.data = order;
					} else {
						this.noOrder = true;
					}
				});
			} else this.navCtrl.pop();
		});
	}

	// getData(id) {

	// 	this.core.showLoading();

	// 	let params = {
	// 		customer: id,
	// 	};

	// 	let getOrder = this.WP.get({
	// 		wcmc: false,
	// 		method: 'GET',
	// 		api: 'orders',
	// 		param: params
	// 	});

	// 	getOrder.subscribe(data => {

	// 		if (data) {

	// 			console.log(data);

	// 			let orders = data.json();

	// 			orders.forEach((v, i) => {

	// 				if (orders[i].meta_data.filter(e => e.key === 'dc_pv_shipped').length > 0) {

	// 					let shipperArray = orders[i].meta_data.find(x => x.key === 'dc_pv_shipped').value;

	// 					if (shipperArray.includes(id)) {
	// 						orders[i].shipped = true;
	// 					}
	// 				}


	// 			});

	// 			if (orders.length > 0) {
	// 				this.noOrder = false;
	// 				this.page++;
	// 				this.data = orders;
	// 			} else {
	// 				this.noOrder = true;
	// 			}

	// 		}

	// 		this.core.hideLoading();

	// 	}, err => {

	// 		this.core.hideLoading();
	// 		this.Toast.showShortBottom(err.json()["message"]).subscribe(
	// 			toast => { },
	// 			error => { console.log(error); }
	// 		);

	// 	});

	// }

	getData(id, hide: boolean = false): Observable<Object[]> {
		return new Observable(observable => {
			if (!hide) this.core.showLoading();

			let params = {
				customer: id,
			};

			let getOrder = this.WP.get({
				wcmc: false,
				method: 'GET',
				api: 'orders',
				param: params
			});

			getOrder.subscribe(data => {
				let orders = data.json();

				orders.forEach((v, i) => {

					if (orders[i].meta_data.filter(e => e.key === 'dc_pv_shipped').length > 0) {

						let shipperArray = orders[i].meta_data.find(x => x.key === 'dc_pv_shipped').value;

						if (shipperArray.includes(id)) {
							orders[i].shipped = true;
						}
					}
				});

				console.log('orders', orders);

				if (!hide) this.core.hideLoading();
				observable.next(orders);
				observable.complete();

			}, err => {
				if (!hide) this.core.hideLoading();
				this.Toast.showShortBottom(err.json()["message"]).subscribe(
					toast => { },
					error => { console.log(error); }
				);
			});

		});
	}

	shop() {
		this.navCtrl.popToRoot();
	}
	load(infiniteScroll) {
		this.getData(this.customer.id, true).subscribe(order => {
			if (order.length > 0) this.page++;
			else this.over = true;
			this.data = this.data.concat(order);
			infiniteScroll.complete();
		});
	}
	doRefresh(refresher) {
		this.page = 1;
		this.getData(this.customer.id, true).subscribe(order => {
			this.over = false;
			if (order.length > 0) this.page++;
			this.data = order;
			refresher.complete();
		});
	}

}
