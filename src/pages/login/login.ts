import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams, AlertController, Platform, ToastController } from 'ionic-angular';
import { Http, Headers } from '@angular/http';
import { Config } from '../../service/config.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { GooglePlus } from '@ionic-native/google-plus';
import { Facebook, FacebookLoginResponse } from '@ionic-native/facebook';
// import { TwitterConnect } from '@ionic-native/twitter-connect';
// Custom
import { Core } from '../../service/core.service';
import { Storage } from '@ionic/storage';
import { TranslateService } from '../../module/ng2-translate';
import { Toast } from '@ionic-native/toast';
import { Content } from 'ionic-angular';

//Page
import { SignupPage } from '../signup/signup';

//additional
import { AngularFireModule } from 'angularfire2';
import firebase from 'firebase';

declare var wordpress_url;
declare var display_mode;
declare var google_web_api_key;
// declare var enable_google_login;
// declare var enable_facebook_login;

@Component({
	selector: 'page-login',
	templateUrl: 'login.html',
	providers: [Core]
})
export class LoginPage {
	wordpress_user: string = wordpress_url + '/wp-json/mobiconnector/user';
	SignupPage = SignupPage;
	formLogin: FormGroup;
	wrong: boolean;
	trans: Object = {};
	login_google: boolean;
	login_facebook: boolean;
	socialMode: boolean = false;
	playerId: string;
	loading: boolean = false;
	constructor(
		public platform: Platform,
		public navCtrl: NavController,
		public config: Config,
		public navParams: NavParams,
		private formBuilder: FormBuilder,
		private http: Http,
		private core: Core,
		private storage: Storage,
		private alertCtrl: AlertController,
		public translate: TranslateService,
		public Toast: Toast,
		public googleplus: GooglePlus,
		private fb: Facebook,
		public toastCtrl: ToastController
	) {
		// this.login_facebook = config['app_settings']['facebook'];
		// this.login_google = config['app_settings']['google'];
		storage.get('userID').then(val => {
			if (val) {
				this.playerId = val;
				http.post(wordpress_url + '/wp-json/mobiconnector/settings/updateplayerid', { 'player_id': this.playerId })
					.subscribe(res => {
						console.log('Update Successful!');
					}, err => {
						this.Toast.showShortBottom(this.trans["login_fail"]).subscribe(
							toast => { },
							error => { console.log(error); }
						);
					});
			}

		});
		this.formLogin = formBuilder.group({
			username: ['', Validators.required],
			password: ['', Validators.required]
		});
		translate.get('login').subscribe(trans => { if (trans) this.trans = trans; });
	}

	login(key: string) {
		if (key == 'normal') {
			this.loginNormal();
		} else if (key == 'facebook') {
			this.loginFacebook();
		} else if (key == 'google') {
			this.loginGP();
		}
	}

	loginNormal() {
		this.core.showLoading();
		this.http.post(wordpress_url + '/wp-json/mobiconnector/jwt/token', this.formLogin.value)
			.subscribe(
				res => {

					console.log('token');
					console.log(res.json());

					let login = res.json();
					login.username = this.formLogin.value.username;
					let params = this.core.objectToURLParams({ 'username': login["username"], 'player_id': this.playerId });
					let headers = new Headers();
					headers.set('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
					headers.set('Authorization', 'Bearer ' + login["token"]);
					this.http.post(this.wordpress_user + '/get_info', params, {
						headers: headers,
						withCredentials: true
					}).subscribe(user => {
						console.log(user.json());
						this.core.hideLoading();
						this.storage.set('user', user.json()).then(() => {
							this.storage.set('login', login).then(() => this.navCtrl.pop());
						});
					}, err => {
						console.log('error oi');
						this.core.hideLoading();
						this.formLogin.patchValue({ password: null });
						this.wrong = true;
					});
				},
				err => {
					this.core.hideLoading();
					this.formLogin.patchValue({ password: null });
					this.wrong = true;
				},

		);
	}
	loginFacebook() {
		this.socialMode = true;
		this.loading = true;
		this.fb.login(['public_profile', 'email'])
			.then((res: FacebookLoginResponse) => {
				console.log(res);
				this.fb.api('me?fields=id,name,email,first_name,last_name,picture', []).then(profile => {
					let params = {
						user_email: profile['email'],
						user_social_id: profile['id'],
						social: 'facebook',
						first_name: profile['first_name'],
						last_name: profile['last_name'],
						display_name: profile['name'],
						user_picture: profile['picture']['data']['url'],
						player_id: this.playerId
					}
					console.log(profile);
					this.socialLogin(params);
				}, err => {
					this.socialMode = false;
					this.loading = false;
					this.Toast.showShortBottom(this.trans["login_fail"]).subscribe(
						toast => { },
						error => { console.log(error); }
					);
				});
			}, err => {
				this.socialMode = false;
				this.loading = false;
				this.Toast.showShortBottom(this.trans["login_fail"]).subscribe(
					toast => { },
					error => { console.log(error); }
				);
			});
	}

	loginGPFirebase() {
		this.socialMode = true;
		this.loading = true;
		this.googleplus.login({
			'webClientId': '645613074065-n9mbjl60isqld2l04olqigldrfjl5pf3.apps.googleusercontent.com',
			'offline': true
		}).then(res => {

			firebase.auth().signInWithCredential(firebase.auth.GoogleAuthProvider.credential(res.idToken))
				.then(success => {

				}).catch(error => {

				});
		});
	}

	loginGP() {
		this.socialMode = true;
		this.loading = true;
		this.googleplus.login({
			'webClientId': '645613074065-n9mbjl60isqld2l04olqigldrfjl5pf3.apps.googleusercontent.com',
			'offline': true
		}).then(profile => {
			let params = {
				user_email: profile['email'],
				user_social_id: profile['userId'],
				social: 'google',
				first_name: profile['familyName'],
				last_name: profile['givenName'],
				display_name: profile['displayName'],
				user_picture: profile['imageUrl'],
				player_id: this.playerId
			}
			console.log(profile);
			this.socialLogin(params);
		}).catch(err => {
			this.socialMode = false;
			this.loading = false;
			console.log(err);
			const toast = this.toastCtrl.create({
				message: err,
				duration: 3000
			});
			toast.present();
			// this.Toast.showShortBottom(this.trans["login_fail"]).subscribe(
			// 	toast => { },
			// 	error => { console.log(error); }
			// );
		});
	}

	socialLogin(params: any) {
		this.http.post(wordpress_url + '/wp-json/mobiconnector/settings/usersociallogin', params)
			.subscribe(
				res => {
					this.loading = false;
					this.storage.set('user', res.json()).then(() => {
						this.storage.set('login', { token: res.json()['token'], socialStatus: params['social'] }).then(() => this.navCtrl.pop());
					});
					console.log(res.json());
				}),
			err => {
				this.loading = false;
				this.socialMode = false;
				this.Toast.showShortBottom(this.trans["login_fail"]).subscribe(
					toast => { },
					error => { console.log(error); }
				);
			}
	}

	forgot() {
		let alert = this.alertCtrl.create({
			title: this.trans["forgot_title"],
			message: this.trans["forgot_body"],
			cssClass: 'alert-forgot',
			inputs: [
				{
					name: 'username',
					placeholder: this.trans["forgot_placeholder"]
				}
			],
			buttons: [
				{
					text: '',
					cssClass: 'button-cancel'
				},
				{
					text: this.trans["forgot_send"],
					cssClass: 'button-confirm',
					handler: data => {
						if (data.username) {
							this.core.showLoading();
							this.http.post(wordpress_url + '/wp-json/mobiconnector/user/forgot_password',
								this.core.objectToURLParams({ username: data.username })
							).subscribe(res => {
								this.core.hideLoading();
								this.Toast.showShortBottom(this.trans["forgot_success"]).subscribe(
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
						} else {
							this.Toast.showShortBottom(this.trans["forgot_err"]).subscribe(
								toast => { },
								error => { console.log(error); }
							);
						}
					}
				}
			]
		});
		alert.present();
	}

}
