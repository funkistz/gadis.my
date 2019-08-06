import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams, AlertController, LoadingController, ToastController } from 'ionic-angular';
import { Http, Headers } from '@angular/http';
import { Content } from 'ionic-angular';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

// Custom
import { Storage } from '@ionic/storage';
import { Core } from '../../service/core.service';
import { Toast } from '@ionic-native/toast';
import { TranslateService } from '../../module/ng2-translate';
import { DetailPage } from '../detail/detail';

declare var wordpress_url: string;
declare var date_format: string;

@Component({
	selector: 'page-detail-order',
	templateUrl: 'detail-order-vendor.html',
	providers: [Core]
})
export class DetailOrderVendorPage {

	formEdit: FormGroup;
	loader: any;

	DetailPage = DetailPage;
	id: Number;
	vendor: Number;
	login: Object;
	data: Object;
	notes = [];
	date_format: string = date_format;
	@ViewChild(Content) content: Content;
	trans: Object;
	shipped: boolean = false;
	shop: any;
	tracking: any = {
		url: '',
		track_number: ''
	};

	constructor(
		public navCtrl: NavController,
		public navParams: NavParams,
		public http: Http,
		public storage: Storage,
		public core: Core,
		public translate: TranslateService,
		public Toast: Toast,
		public alertCtrl: AlertController,
		public WP: WoocommerceProvider,
		public formBuilder: FormBuilder,
		public loadingCtrl: LoadingController,
		public toastCtrl: ToastController
	) {

		this.formEdit = formBuilder.group({
			url: ['', Validators.compose([Validators.maxLength(50), Validators.required])],
			track_number: ['', Validators.compose([Validators.maxLength(20), Validators.required])],
		});

		translate.get('detailOrder.popup_cancel').subscribe(trans => this.trans = trans);
		this.id = navParams.get('id');
		this.vendor = navParams.get('vendor');

		core.showLoading();
		storage.get('login').then(val => {
			console.log(val);
			if (val && val['token']) {
				this.login = val;
				this.getData();
			} else navCtrl.pop();
		});
	}

	getData() {
		this.core.showLoading();

		let params = {
			vendor: this.vendor,
		};

		let getOrder = this.WP.get({
			wcmc: false,
			method: 'GET',
			api: 'orders/' + this.id,
			param: params
		});

		getOrder.subscribe(res => {

			console.log('detail order vendor');
			console.log(res.json());

			this.data = res.json();
			this.core.hideLoading();
			this.content.resize();

			if (res.json().meta_data.filter(e => e.key === 'dc_pv_shipped').length > 0) {

				console.log(res.json().meta_data.find(x => x.key === 'dc_pv_shipped').value);

				let shipperArray = res.json().meta_data.find(x => x.key === 'dc_pv_shipped').value;

				if (shipperArray.includes(this.vendor)) {
					this.shipped = true;
				}
			}

		}, err => {

			console.log(err);

		});

		let getOrderNotes = this.WP.get({
			wcmc: false,
			method: 'GET',
			api: 'orders/' + this.id + '/notes',
			param: params
		});

		getOrderNotes.subscribe(res => {

			console.log('order notes');
			console.log(res.json());

			this.notes = res.json();

		}, err => {

			console.log(err);

		});

	}

	markShipped() {

		this.loader = this.loadingCtrl.create({
			content: 'Please wait...',
		});
		this.loader.present();

		let temp = this.formEdit.value;

		let params: any = {
			vendor_id: this.vendor,
			order_id: this.id,
			url: temp.url,
			track_number: temp.track_number,
		}

		let updateRequest = this.http.post(wordpress_url + '/wcmp-order.php',
			params
		).subscribe(response => {

			this.loader.dismiss();
			console.log(response);
			if (response) {

				console.log(response);
				if (response.json().status == 'success') {
					this.presentToast(response.json().message);

					this.getData();
				} else {
					this.presentToast('Some error occured');
				}

			}

		});

	}

	changeStatus() {
		let alert = this.alertCtrl.create({
			message: this.trans['message'],
			cssClass: 'alert-no-title alert-cancel-order',
			buttons: [
				{
					text: this.trans['no']
				},
				{
					text: this.trans['yes'],
					cssClass: 'primary',
					handler: () => {
						this.core.showLoading();
						let params = this.core.objectToURLParams({ order: this.id });
						let headers = new Headers();
						headers.set('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
						headers.set('Authorization', 'Bearer ' + this.login["token"]);
						this.http.post(wordpress_url + '/wp-json/wooconnector/order/changestatus', params, {
							headers: headers,
							withCredentials: true
						}).subscribe(res => {
							this.core.hideLoading();
							if (res.json()['result'] == 'success') {
								this.Toast.showShortBottom(this.trans["success"]).subscribe(
									toast => { },
									error => { console.log(error); }
								);
								this.navCtrl.pop();
							} else {
								this.Toast.showShortBottom(this.trans["fail"]).subscribe(
									toast => { },
									error => { console.log(error); }
								);
							}
						});
					}
				}
			]
		});
		alert.present();
	}

	doRefresh(refresher) {
		this.getData();
		refresher.complete();
	}

	presentToast(text) {
		let toast = this.toastCtrl.create({
			message: text,
			duration: 3000,
			position: 'top'
		});
		toast.present();
	}

}
