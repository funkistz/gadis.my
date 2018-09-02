import { Http, Headers } from '@angular/http';
import { Injectable } from '@angular/core';
import { URLSearchParams } from '@angular/http';
import { LoadingController, Platform } from 'ionic-angular';
import { Observable } from 'rxjs/Observable';
import { Storage } from '@ionic/storage';
// Custom
import { Network } from '@ionic-native/network';
import { Config } from './config.service';
import { Toast } from '@ionic-native/toast';
declare var request_timeout: Number;
declare var wordpress_url;
@Injectable()
export class Core {
	loading: any;
	isLoading:boolean;
	constructor(
		public loadingCtrl: LoadingController,
		public platform: Platform,
		private Network: Network,
		config: Config,
	  	public storage: Storage,
	  	public Toast: Toast,
	  	public http: Http
	) {}
	objectToURLParams(object): URLSearchParams {
		let params: URLSearchParams = new URLSearchParams();
		for (var key in object) {
			if (object.hasOwnProperty(key)) {
				if(Array.isArray(object[key])){
					object[key].forEach(val => {
						params.append(key+'[]', val);
					});
				}
				else params.set(key, object[key]);
			}
		}
		return params;
	}
	showLoading(content:string = null) {
		if(!this.isLoading){
			this.isLoading = true;
			this.loading = this.loadingCtrl.create({
				content: content
			});
			this.loading.onDidDismiss(() => {
				this.isLoading = false
			});
			this.loading.present();
			setTimeout(() => { this.hideLoading() }, request_timeout);
			this.platform.ready().then(() => {
				if(this.Network.type == "none") this.hideLoading();
				this.Network.onDisconnect().subscribe(() => { this.hideLoading(); });
			});
		}
	}
	hideLoading() { if(this.isLoading) this.loading.dismiss(); }
	getVariation(variations:Object[], attributes:Object[]):Observable<Object> {
		return new Observable(observable => {
			let variation:any;
			let _attr = JSON.stringify(attributes).toLowerCase();
			let maxEqual = 0;
			variations.forEach(val => {
				let equalAttr = 0;
				val["attributes"].forEach(attr => {
					console.log(_attr);
					console.log(attr);
					delete attr['taxanomy'];
					delete attr['options_slug'];
					if(_attr.indexOf(JSON.stringify(attr).toLowerCase()) != -1) equalAttr++;
				});
				if(equalAttr > maxEqual && equalAttr == val["attributes"].length) {
					variation = val;
					maxEqual = equalAttr;
				}
			});
			if(!variation) variation = variations.filter(
				item => item["attributes"].length == 0
			)[0];
			observable.next(variation);
			observable.complete();
		});
	}
	filterProfile(profile:Object):any {
		if(!profile) profile = {};
		return profile;
	}
	addSortToSearchParams(params: Object, sort_type: String) {
    	if(sort_type == "-date") {
            params['post_order_by'] = 'date';
            params['post_order_page'] = 'ASC';
        } else if (sort_type == 'price') {
            params['price_sort'] = 1;
            params['post_order_page'] = 'ASC';
        } else if (sort_type == '-price') {
            params['price_sort'] = 1;
            params['post_order_page'] = 'DESC';
        } else if (sort_type == '-name') {
            params['name_sort'] = 1;
            params['post_order_page'] = 'DESC';
        } else if (sort_type == 'name') {
            params['name_sort'] = 1;
            params['post_order_page'] = 'ASC';
        } else if (sort_type == 'popularity') {
        	params['popularity_sort'] = 1;
        	params['post_order_page'] = 'DESC';
        } else if (sort_type == 'rating') {
        	params['rating_sort'] = 1;
        	params['post_order_page'] = 'DESC';
        }
        return params;
    }
    removeToken() {
	  	this.storage.remove('user').then(() => {
			this.storage.remove('login').then(() => {
				// this.Toast.showShortBottom(message)
			});
		});
	}
  	checkTokenLogin(token: string):Observable<any> {
	  	return new Observable(observable => {
	  		let headers = new Headers();
		  		headers.set('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
				headers.set('Authorization', 'Bearer ' + token);
	  		this.http.post(wordpress_url + '/wp-json/mobiconnector/jwt/token/validate',{}, {
	  			headers: headers,
				withCredentials: true
	  		}).subscribe(data => {
	  			observable.next(data.json());
	            observable.complete();
	  		}, err => {
	  			console.log(err.json());
	  			observable.next(err.json());
	            observable.complete();
	  		});
	  	});
  	}
}