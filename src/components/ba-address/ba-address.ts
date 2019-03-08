import { Component, Input } from '@angular/core';
import { NavController, NavParams, Platform } from 'ionic-angular';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { Http, Headers } from '@angular/http';
import { CoreValidator } from '../../validator/core';

//Pipes
import { ObjectToArrayPipe } from '../../pipes/object-to-array/object-to-array';

import { Storage } from '@ionic/storage';
import { StorageMulti } from '../../service/storage-multi.service';
import { Core } from '../../service/core.service';
import { Config } from '../../service/config.service';
import { TranslateService } from '../../module/ng2-translate';
import { Geolocation } from '@ionic-native/geolocation';
import { LocationAccuracy } from '@ionic-native/location-accuracy';
import { Diagnostic } from '@ionic-native/diagnostic';
import { Device } from '@ionic-native/device';
import { Toast } from '@ionic-native/toast';

// Page
import { LoginPage } from '../../pages/login/login';
import { CheckoutPage } from '../../pages/checkout/checkout';

declare var wordpress_url;
declare var display_mode;

@Component({
	selector: 'ba-address',
	templateUrl: 'ba-address.html',
	providers: [Core, StorageMulti, Geolocation, LocationAccuracy, Diagnostic, Device, ObjectToArrayPipe]
})
export class BaAddressComponent {
	@Input() cssClass: string;
	LoginPage = LoginPage;
	CheckoutPage = CheckoutPage;
	formAddress: FormGroup;
	login: Object = {};
	data: Object = {};
	dataUser: Object = {};
	rawData: Object;
	isCache: boolean;
	useBilling: boolean;
	statesBillingCustom: Object = {};
	statesBilling: Object = {};
	statesShippingCustom: Object = {};
	statesShipping: Object = {};
	countries: Object[] = [];
	states: Object = {};
	trans: Object;
	display_mode: string;
	shippingStateRequired = false;
	billingStateRequired = false;
	checked: Object = {};
	constructor(
		public navCtrl: NavController,
		public navParams: NavParams,
		public http: Http,
		public storage: Storage,
		public storageMul: StorageMulti,
		public formBuilder: FormBuilder,
		public core: Core,
		public config: Config,
		public translate: TranslateService,
		public Geolocation: Geolocation,
		public LocationAccuracy: LocationAccuracy,
		public platform: Platform,
		public Diagnostic: Diagnostic,
		public Device: Device,
		public Toast: Toast
	) {
		this.display_mode = display_mode;
		translate.get('states').subscribe(trans => {
			if (trans == 'states') trans = {};
			if (config['countries']) this.countries = config['countries'];
			this.states = Object.assign(trans, config['states']);
		});
		translate.get('address.location').subscribe(trans => this.trans = trans);
		if (config['customForm']) {
			this.data = this.config['customForm'];
			let params: Object = {};
			this.data['billing'].forEach(item => {
				if (item['required_check'] == 1) {
					if (item['name_id'] != 'billing_email') params[item['name_id']] = ['', Validators.required];
					else if (item['name_id'] == 'billing_email') params[item['name_id']] = ['', Validators.compose([Validators.required, CoreValidator.isEmail])];
				} else params[item['name_id']] = ['']
			});
			this.data['shipping'].forEach(item => {
				if (item['required_check'] == 1) {
					if (item['name_id'] != 'shipping_email') params[item['name_id']] = ['', Validators.required];
					else if (item['name_id'] == 'shipping_email') params[item['name_id']] = ['', Validators.compose([Validators.required, CoreValidator.isEmail])];
				} else params[item['name_id']] = ['']
			});
			this.formAddress = this.formBuilder.group(params);
		}
		this.getDataForm();
	}

	getDataForm() {
		this.storageMul.get(['login', 'useBilling', 'user']).then(val => {
			if (val['login']) this.login = val['login'];
			if (val['useBilling'] == false) this.useBilling = false;
			else this.useBilling = true;
			if (val['user']) {

				console.log(val['user'], 'user data');

				this.dataUser = val['user'];
				this.checkBillingDefault(this.dataUser['mobiconnector_address']['billing_country']);
				Object.keys(this.dataUser['mobiconnector_address']).forEach(item => {
					this.data['billing'].forEach(field => {
						if (item.indexOf('billing_country') == 0 && item != 'billing_country') {
							if (item == field['name_id']) {
								console.log(field['name_id']);
								this.checkBillingCustom(this.dataUser['mobiconnector_address'][field['name_id']], field['name_id'], field['country_has_state']);
							}
						}
					});
				});
				this.updateShipping();
				this.reset();

				console.log(this.dataUser['user_email']);
				if (this.dataUser['user_email']) {
					this.formAddress.get('billing_email').setValue(this.dataUser['user_email']);
					this.formAddress.get('shipping_email').setValue(this.dataUser['user_email']);
				}
			}
		});
	}
	// click(name_id: string) {
	// 	let data: Object = [];
	// 	let params: Object = {};
	// data[name_id] = this.optionsBilling;
	// console.log(data[name_id].length);
	// params[name_id] = data[name_id];
	// console.log(this.optionsBilling);
	// data[name_id].forEach(item => {
	// 	console.log('aaa');
	// });
	// this.formAddress.patchValue(params);
	// return this.dataCheckbox = params[name_id] = JSON.stringify(dataCheckbox[name_id]);


	// }
	reset() {
		this.formAddress.patchValue(this.dataUser['mobiconnector_address']);
		this.rawData = Object.assign({}, this.formAddress.value);
	}

	updateShipping() {
		if (this.useBilling) {
			this.statesShippingCustom = this.statesBillingCustom;
			Object.keys(this.statesBilling).forEach(value => {
				let attr = 'shipping' + value.slice(7);
				this.statesShipping[attr] = this.statesBilling[value];
			});
			let params: Object = {};
			this.data['shipping'].forEach(shipping_item => {
				this.data['billing'].forEach(billing_item => {
					if (shipping_item['name_id'].slice(8) == billing_item['name_id'].slice(7)) {
						params[shipping_item['name_id']] = this.formAddress.value[billing_item['name_id']];
					}
				});
			});
			this.formAddress.patchValue(params);
		}
	}
	checkUseBilling() {
		if (this.useBilling) this.updateShipping();
	}
	changeCountryBilling(e, country: string, state: string) {
		if (country == 'billing_country' && state == 'billing_state') this.checkBillingDefault(e);
		else this.checkBillingCustom(e, country, state);

	}

	checkBillingDefault(e) {
		if (this.states[e]) {
			this.statesBillingCustom['default_country'] = this.states[e];
			this.billingStateRequired = true;
			this.formAddress.patchValue({ billing_state: '' });
		} else {
			this.statesBillingCustom['default_country'] = null;
			this.billingStateRequired = false;
			this.formAddress.patchValue({ billing_state: '' });
		}
		if (this.useBilling) this.formAddress.patchValue({
			shipping_country: this.formAddress.value["billing_country"]
		});
	}
	checkBillingCustom(e, country: any, state: any) {
		if (this.states[e]) {
			this.statesBilling[country] = this.states[e];
			this.billingStateRequired = true;
			let data: Object = {};
			data[state] = '';
			this.formAddress.patchValue(data);
		} else {
			this.statesBilling[country] = null;
			this.billingStateRequired = false;
			let data: Object = {};
			data[state] = '';
			this.formAddress.patchValue(data);
		}
		// if (this.useBilling) this.formAddress.patchValue({
		//   	shipping_country: this.formAddress.value["billing_country"]
		// });
		if (this.useBilling) {
			let data: Object = {};
			let name_id = 'shipping' + country.slice(7)
			data[name_id] = this.formAddress.value[country];
			this.formAddress.patchValue(data[name_id]);
		}
	}
	changeCountryShipping(e, country: string, state: string) {
		if (country == 'shipping_country' && state == 'shipping_state') this.checkShippingDefault(e);
		else this.checkShippingCustom(e, country, state);
	}
	checkShippingDefault(e) {
		if (this.states[e]) {
			this.statesShippingCustom['default_country'] = this.states[e];
			console.log(this.statesShipping);
			this.shippingStateRequired = true;
			this.formAddress.patchValue({ shipping_state: '' });
		} else {
			this.statesShippingCustom['default_country'] = null;
			this.shippingStateRequired = false;
			this.formAddress.patchValue({ shipping_state: '' });
		}
	}
	checkShippingCustom(e, country: any, state: any) {
		if (this.states[e]) {
			this.statesShipping[country] = this.states[e];
			this.shippingStateRequired = true;
			let data: Object = {};
			data[state] = '';
			this.formAddress.patchValue(data);
		} else {
			this.statesShipping[country] = null;
			this.shippingStateRequired = false;
			let data: Object = {};
			data[state] = '';
			this.formAddress.patchValue(data);
		}
	}
	changeBillingState(e, country: string, state: string) {
		if (this.useBilling) {
			console.log(this.statesBillingCustom);
			console.log(this.statesShippingCustom);
			let data: Object = {};
			let name_id = 'shipping' + state.slice(7);
			data[name_id] = this.formAddress.value[state];
			console.log(data);
			console.log(name_id);
			this.formAddress.patchValue(data);
		}
	}
	confirm() {
		this.storage.set('useBilling', this.useBilling);
		if (this.useBilling) this.updateShipping();
		if (JSON.stringify(this.rawData) != JSON.stringify(this.formAddress.value)) {
			if (this.login["token"]) {
				let paramsAddress = this.formAddress.value;
				paramsAddress['ship_to_different_address'] = 1;
				console.log(paramsAddress);
				let params = this.core.objectToURLParams(paramsAddress);
				console.log(params);
				let headers = new Headers();
				headers.set('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
				headers.set('Authorization', 'Bearer ' + this.login["token"]);
				this.core.showLoading();
				this.http.post(wordpress_url + '/wp-json/wooconnector/user/update_profile_form', params, {
					headers: headers,
					withCredentials: true
				}).subscribe(res => {
					this.data = res.json();
					this.storage.set('user', this.data).then(() => {
						this.gotoCheckout();
					});
					this.core.hideLoading();
				}, err => {

					this.core.hideLoading();
					this.Toast.showShortBottom(err.json()["message"]).subscribe(
						toast => { },
						error => { console.log(error); }
					);
				});
			} else {
				this.dataUser['mobiconnector_address'] = this.formAddress.value;
				console.log(this.data);
				this.storage.set('user', this.dataUser).then(() => {
					this.gotoCheckout();
				});
			}
		} else this.gotoCheckout();
	}
	gotoCheckout() {
		if (this.navCtrl.getPrevious() && this.navCtrl.getPrevious().component == this.CheckoutPage)
			this.navCtrl.pop();
		else {
			this.navCtrl.push(this.CheckoutPage).then(() => {
				this.navCtrl.remove(this.navCtrl.getActive().index - 1);
			});
		}
	}
	location() {
		if (!this.platform.is('cordova')) return;
		this.core.showLoading();
		this.LocationAccuracy.canRequest().then(can => {
			if ((!can && this.Device.platform == 'iOS') || (can && this.Device.platform == 'Android')) {
				this.LocationAccuracy.request(this.LocationAccuracy.REQUEST_PRIORITY_HIGH_ACCURACY).then(() => {
					this.Geolocation.getCurrentPosition({ enableHighAccuracy: true }).then(resp => {
						let latlng;
						if (resp['coords']) latlng = resp['coords']['latitude'] + ',' + resp['coords']['longitude'];
						if (!latlng) return;
						this.http.get('http://maps.google.com/maps/api/geocode/json?latlng=' + latlng).subscribe(res => {
							if (res.json()['status'] == 'OK' && res.json()['results']) {
								let address = res.json()['results'][0];
								let city;
								let country;
								address['address_components'].forEach(component => {
									if (component['types'].indexOf('administrative_area_level_1') != -1)
										city = component['long_name'];
									if (component['types'].indexOf('country') != -1)
										country = component['short_name'];
								});
								this.formAddress.patchValue({
									billing_address_1: address['formatted_address'],
									billing_city: city,
									billing_country: country
								});
							}
						});
						this.core.hideLoading();
					}).catch((error) => {
						this.core.hideLoading();
					});
				}, err => this.core.hideLoading());
			} else {
				this.Diagnostic.requestLocationAuthorization('always').then(res => {
					if (res) this.location();
				});
				this.core.hideLoading();
			}
		});
	}

}
