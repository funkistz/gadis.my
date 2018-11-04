import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { Http } from '@angular/http';


// Custom
import { Core } from '../../service/core.service';
import { Storage } from '@ionic/storage';
import { StorageMulti } from '../../service/storage-multi.service';

// Page
import { DetailCategoryPage } from '../detail-category/detail-category';
import { SearchPage } from '../search/search';
import moment from 'moment';

declare var wordpress_url: string;

@Component({
	selector: 'page-categories',
	templateUrl: 'categories.html',
	providers: [Core, StorageMulti]
})
export class CategoriesPage {

	@ViewChild('cart') buttonCart;
	DetailCategoryPage = DetailCategoryPage;
	SearchPage = SearchPage;
	parents: Object[] = [];
	id: Number;
	noResuilt: boolean = false;
	faded: boolean = false;
	loaddata: boolean = false;
	title = 'All Categories';
	public today = moment();
	public date: string = new Date().toISOString();

	constructor(
		public http: Http,
		public core: Core,
		public navCtrl: NavController,
		private navParams: NavParams,
		public storage: Storage,
		public storageMul: StorageMulti
	) {

		this.storageMul.get(['last_sync_all_category', 'all_category']).then(val => {

			if (val['last_sync_all_category'] && val['all_category']) {

				if (!this.today.isSame(new Date(val['last_sync_all_category']), "day")) {

					console.log('not today all category');
					this.getCategory();
				} else {

					this.loaddata = true;
					let tempCat = this.parents.concat(val['all_category']);

					this.parents = tempCat.filter(function (obj: any) {
						return obj.parent != 0;
					});

					setTimeout(() => {
						this.faded = true;
					}, 100);
					if (val['all_category'] && val['all_category'].length == 100) {
						this.noResuilt = false;
						this.getCategory();
					} else {
						this.loaddata = true;
						this.noResuilt = true;
					}
				}

			} else {

				this.getCategory();

			}

		});

	}

	getCategory() {

		console.log('get category...');

		let id = this.navParams.get('id');
		let name = this.navParams.get('name');
		let params: any = { cat_num_page: 1, cat_per_page: 100, cat_order_by: 'slug' };

		if (id) {
			params = { cat_num_page: 1, cat_per_page: 100, cat_order_by: 'slug', parent: id };
			this.title = name;
		}

		this.http.get(wordpress_url + '/wp-json/wooconnector/product/getcategories', {
			search: this.core.objectToURLParams(params)
		}).subscribe(res => {

			this.storage.set('last_sync_all_category', this.date);
			this.storage.set('all_category', res.json());
			this.loaddata = true;
			let tempCat = this.parents.concat(res.json());

			this.parents = tempCat.filter(function (obj: any) {
				return obj.parent != 0;
			});

			setTimeout(() => {
				this.faded = true;
			}, 100);
			if (res.json() && res.json().length == 100) {
				this.noResuilt = false;
				params.cat_num_page++;
				this.getCategory();
			} else {
				this.loaddata = true;
				this.noResuilt = true;
			}
		});

	}

	ionViewDidEnter() {
		this.buttonCart.update();
	}
	onSwipeContent(e) {
		if (e['deltaX'] < -150 || e['deltaX'] > 150) {
			if (e['deltaX'] < 0) this.navCtrl.push(this.SearchPage);
			else this.navCtrl.popToRoot();
		}
	}

}
