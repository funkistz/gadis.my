import { Component, Input } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { Http, Headers } from '@angular/http';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Config } from '../../service/config.service';
// Custom
import { Storage } from '@ionic/storage';
import { Core } from '../../service/core.service';
import { CoreValidator } from '../../validator/core';
import { Toast } from '@ionic-native/toast';
import { TranslateService } from '../../module/ng2-translate';
import { Camera } from '@ionic-native/camera';

declare var wordpress_url: string;

@Component({
	selector: 'ba-profile',
	templateUrl: 'ba-profile.html'
})
export class BaProfileComponent {
	@Input() cssClass: string;
	wordpress_user: string = wordpress_url + '/wp-json/mobiconnector/user';
	login: Object;
	data: Object;
	formEdit: FormGroup;
	avatar: string;
	trans: string;
	dataForm: Object = {};
	checkData: boolean;
	transCountry: Object;
	states: Object = {};
	stateProfile: Object = {};
	countries: Object[] = [];
	constructor(
		public navCtrl: NavController,
		public navParams: NavParams,
		private storage: Storage,
		private http: Http,
		private core: Core,
		private formBuilder: FormBuilder,
		translate: TranslateService,
		private Toast: Toast,
		private Camera: Camera,
		public config: Config
	) {
		translate.get('states').subscribe(trans => {
			if (trans == 'states') trans = {};
			if (config['countries']) this.countries = config['countries'];
			this.states = Object.assign(trans, config['states']);
		});
		translate.get('profile.update_successfully').subscribe(trans => this.trans = trans);

		console.log('test once');

		storage.get('login').then(val => {
			if (val && val["token"]) {
				console.log('test 1');

				this.login = val;
				core.showLoading();
				storage.get('user').then(user => {
					console.log(user);
					console.log('test 2');

					core.hideLoading();
					if (user && user["ID"]) {
						console.log(this.dataForm);
						this.data = user;
						console.log(this.data['mobiconnector_info']);
						if (config['customForm']) {
							console.log('test 4');

							console.log(this.dataForm);
							this.dataForm = config['customForm'];
							let params: Object = {};
							this.dataForm['profile'].forEach(item => {
								if (item['required_check'] == 1) {
									if (item['name_id'] != 'user_email') params[item['name_id']] = ['', Validators.required];
									else if (item['name_id'] == 'user_email') params[item['name_id']] = ['', Validators.compose([Validators.required, CoreValidator.isEmail])];
								} else params[item['name_id']] = [''];
							});
							console.log(params);
							this.formEdit = formBuilder.group(params);
							this.reset();
							this.checkData = true;
						}
					} else navCtrl.pop();
				});
			} else navCtrl.pop();
		});
	}

	reset() {
		let data: Object = {};
		Object.keys(this.data['mobiconnector_info']).forEach(item => {
			data['billing_' + item] = this.data['mobiconnector_info'][item];
		});
		// Object.keys(this.data['mobiconnector_address']).forEach(item => {
		// 	data[item] = this.data['mobiconnector_address'][item];
		// 	console.log(item);
		// 	console.log(this.data['mobiconnector_address'][item]);
		// });
		Object.keys(this.data['field_extra_user']).forEach(item => {
			data[item] = this.data['field_extra_user'][item];
		});
		Object.keys(data).forEach(item => {
			this.dataForm['profile'].forEach(field => {
				if (item != 'billing_country' && item.indexOf('billing_country') == 0) {
					if (item == field['name_id']) this.changeCountryprofile(data[item], item, field['country_has_state']);
				}
			});
		});
		console.log(data);
		this.formEdit.patchValue(data);
		this.avatar = this.data['mobiconnector_info']["mobiconnector_avatar"];
	}
	editAvatar() {
		this.Camera.getPicture({
			quality: 100,
			sourceType: 0,
			allowEdit: true,
			targetWidth: 180,
			targetHeight: 180,
			destinationType: 0
		}).then((imageData) => {
			this.avatar = 'data:image/jpeg;base64,' + imageData;
		}, (err) => { });
	}
	changeCountryprofile(e, country: string, state: string) {
		this.checkCountryCustom(e, country, state);
	}
	checkCountryCustom(e, country: string, state: string) {
		if (this.states[e]) {
			this.stateProfile[country] = this.states[e];
			let data: Object = {};
			data[state] = '';
			this.formEdit.patchValue(data);
		} else {
			this.stateProfile[country] = null;
			let data: Object = {};
			data[state] = '';
			this.formEdit.patchValue(data);
		}
	}

	save() {
		this.core.showLoading();
		let params: Object = {};
		Object.keys(this.formEdit.value).forEach(value => {
			if (value.indexOf('billing') == 0) params[value.slice(8)] = this.formEdit.value[value];
			else params[value] = this.formEdit.value[value];
		});
		// params["display_name"] = params["first_name"] + " " + params["last_name"];
		if (this.avatar != this.data['mobiconnector_info']["mobiconnector_avatar"]) params["user_profile_picture"] = this.avatar;
		params = this.core.objectToURLParams(params);
		let headers = new Headers();
		headers.set('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		headers.set('Authorization', 'Bearer ' + this.login["token"]);
		this.http.post(this.wordpress_user + '/update_profile_form', params, {
			headers: headers,
			withCredentials: true
		}).subscribe(res => {
			this.core.hideLoading();
			this.data = res.json();
			console.log(this.data);
			this.storage.set('user', this.data);
			this.Toast.showShortBottom(this.trans).subscribe(
				toast => { },
				error => { console.log(error); }
			);
		}, err => {
			this.core.hideLoading();
			this.Toast.showShortBottom(err.json()["message"]).subscribe(
				toast => { },
				error => { console.log(error); }
			);
		});
	}

}
