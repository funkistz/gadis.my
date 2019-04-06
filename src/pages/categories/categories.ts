import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { Http } from '@angular/http';


// Custom
import { Core } from '../../service/core.service';
import { Storage } from '@ionic/storage';
import { Toast } from '@ionic-native/toast';
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
	parents: any[] = [];
	parentsFiltered: any[] = [];
	id: Number;
	noResuilt: boolean = false;
	faded: boolean = false;
	loaddata: boolean = false;
	title = 'All Categories';
	public date: string = new Date().toISOString();
	public today = moment();
	parent_id;

	constructor(
		public http: Http,
		public core: Core,
		public navCtrl: NavController,
		public navParams: NavParams,
		public storage: Storage,
		public storageMul: StorageMulti
	) {

		this.parent_id = this.navParams.get('id');

		if (this.parent_id) {
			this.title = this.navParams.get('name');
		}

		this.loadCategories();
	}
	ionViewDidEnter() {
		this.today = moment();
		this.buttonCart.update();
	}
	onSwipeContent(e) {
		if (e['deltaX'] < -150 || e['deltaX'] > 150) {
			if (e['deltaX'] < 0) this.navCtrl.push(this.SearchPage);
			else this.navCtrl.popToRoot();
		}
	}

	cat_num_page = 1;
	loadCategories() {

		console.log('load categories...');

		this.storageMul.get(['last_sync_all_category', 'all_category']).then(val => {

			if (val['last_sync_all_category'] && val['all_category']) {

				if (!this.today.isSame(new Date(val['last_sync_all_category']), "day")) {

					console.log('not today all category');
					this.getAllCategory();

				} else {

					this.parents = val['all_category'];

					if (this.parent_id) {
						let items = val['all_category'].filter(item => item.parent == this.parent_id);
						this.parentsFiltered = items;
					} else {
						this.parentsFiltered = val['all_category'];
					}

					console.log(this.parentsFiltered);

					setTimeout(() => {
						this.faded = true;
					}, 100);
					this.loaddata = true;
					this.noResuilt = true;

				}

			} else {

				this.getAllCategory();

			}

		});

	};

	getAllCategory() {

		let loadCategories = () => {

			console.log('load categories...');

			let params = { cat_num_page: this.cat_num_page, cat_per_page: 100 };
			this.http.get(wordpress_url + '/wp-json/wooconnector/product/getcategories', {
				search: this.core.objectToURLParams(params)
			}).subscribe(res => {
				this.loaddata = true;
				this.parents = this.parents.concat(res.json());

				if (this.parent_id) {
					let items = res.json().filter(item => item.parent == this.parent_id);
					this.parentsFiltered = this.parentsFiltered.concat(items);
				} else {
					this.parentsFiltered = this.parentsFiltered.concat(res.json());
				}

				setTimeout(() => {
					this.faded = true;
				}, 100);
				if (res.json() && res.json().length == 100) {
					this.noResuilt = false;
					this.cat_num_page++;
					loadCategories();
				} else {
					this.loaddata = true;
					this.noResuilt = true;

					this.storage.set('all_category', this.parents);
					this.storage.set('last_sync_all_category', this.date);
				}
			});
		};
		loadCategories();

	}

	categoryPage(category) {

		// [navPush]="DetailCategoryPage" [navParams]="{id:category.id}"

		let items = this.parents.filter(item => item.parent == category.id);

		if (items && items.length) {

			this.navCtrl.push(CategoriesPage, { id: category.id, name: category.name });

		} else {

			this.navCtrl.push(this.DetailCategoryPage, { id: category.id });

		}

		console.log(items);

	}

}
