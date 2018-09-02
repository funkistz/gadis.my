import { Component, Input} from '@angular/core';
import { NavController } from 'ionic-angular';
import { Http, Headers } from '@angular/http';
import { Core } from '../../service/core.service';
import { Storage } from '@ionic/storage';
import { Config } from '../../service/config.service';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
// Custom
import { TranslateService } from '../../module/ng2-translate';
import { Toast } from '@ionic-native/toast';
import { LoginPage } from '../../pages/login/login';
import { CoreValidator } from '../../validator/core';
declare var wordpress_url;
declare var display_mode;

@Component({
  selector: 'ba-register',
  templateUrl: 'ba-register.html',
  providers: [Core]
})
export class BaRegisterComponent {

  	@Input() cssClass: string = '';
  	LoginPage = LoginPage;
	formSignup: FormGroup;
	trans:Object;
	states: Object = {};
	stateRegisterDefault:Object = {};
	stateRegister:Object = {};
	data: Object = {};
	countries: Object[] = [];
	checked: Object = {};

  constructor(
  		public navCtrl: NavController,
		public http: Http,
		public core: Core,
		public storage: Storage,
		public formBuilder: FormBuilder,
		public config: Config,
		public translate: TranslateService,
		public Toast: Toast
  	) {
  		translate.get('states').subscribe(trans => {
	      	if (trans == 'states') trans = {};
	      	if (config['countries']) this.countries = config['countries'];
	      	this.states = Object.assign(trans, config['states']);
	    });
    	translate.get('signup').subscribe(trans => this.trans = trans);
    	this.generateForm();
  }

  	generateForm() {
  		if (this.config['customForm']) {
			this.data = this.config['customForm'];
			console.log(this.data['register']);
			let params: Object = {};
			let repass: Object = {
				name_id: 'baform_re_password',
				type: 'password',
				require_check: 1
			}
			this.data['register'].forEach((item, key) => { 
				if (item['required_check'] == 1) {
					if (item['name_id'] != 'billing_email' && item['name_id'] != 'baform_re_password') params[item['name_id']] = ['', Validators.required];
					else if (item['name_id'] == 'billing_email') params[item['name_id']] = ['', Validators.compose([Validators.required, CoreValidator.isEmail])];
					else if (item['name_id'] == 'baform_re_password')  params[item['name_id']] = ['', Validators.compose([Validators.required, CoreValidator.confirmPassword])];
				} else  {
					if (item['name_id'] == 'billing_password') params[item['name_id']] = ['', Validators.required];
					else params[item['name_id']] = [''];
				}
			});	
			console.log(params);
			this.formSignup = this.formBuilder.group(params);
		}
  	}
  	removeConfirm(){
		this.formSignup.patchValue({ baform_re_password: null });
	}
	register(){
		let params : Object = {};
		Object.keys(this.formSignup.value).forEach(value => {
			if (value.indexOf('billing') == 0) params[value.slice(8)] = this.formSignup.value[value]; 
			else params[value] = this.formSignup.value[value]; 
		});
		params["display_name"] = params["first_name"] + ' ' + params["last_name"];
		console.log(params);
		params = this.core.objectToURLParams(params);
		this.core.showLoading();
		this.http.post(wordpress_url+'/wp-json/mobiconnector/user/register_form', params)
		.subscribe(res => {
			this.core.hideLoading();
			this.Toast.showShortBottom(this.trans["success"]).subscribe(
				toast => {},
				error => {console.log(error);}
			);
			this.gotoLogin();
		}, err => {
			this.core.hideLoading();
			this.Toast.showShortBottom(err.json()["message"]).subscribe(
				toast => {},
				error => {console.log(error);}
			);
		});
	}
	changeCountryRegister(e, country: string, state: string) {
 		if (country == 'billing_country' && state == 'billing_state') this.checkCountryDefault(e);
	    else this.checkCountryCustom(e, country, state);
	}
	checkCountryDefault(e) {
		console.log(this.states[e]);
		if (this.states[e]) {
		    this.stateRegister['default_country'] = this.states[e];
		    this.formSignup.patchValue({billing_state: ''});
	    } else {
	    	this.stateRegister['default_country'] = null;
	      	this.formSignup.patchValue({billing_state: ''});
	    }

	}
	checkCountryCustom(e, country: string, state: string) {
		if (this.states[e]) {
		    this.stateRegister[country] = this.states[e];
		    console.log(this.stateRegister);
		    let data: Object = {};
		    data[state] = '';
		    this.formSignup.patchValue(data);
	    } else {
	    	this.stateRegister[country] = null;
	      	let data: Object = {};
		    data[state] = '';
	      	this.formSignup.patchValue(data);
	    }
	}
	gotoLogin(){
		if(this.navCtrl.getPrevious() && this.navCtrl.getPrevious().component == this.LoginPage)
			this.navCtrl.pop();
		else this.navCtrl.push(this.LoginPage);
	}

}
