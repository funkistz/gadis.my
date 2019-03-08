import { Component, ViewChild } from '@angular/core';
import { Http } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import { NavController, NavParams, TextInput } from 'ionic-angular';


// Custom
import { Core } from '../../service/core.service';
import { Storage } from '@ionic/storage';
import { Toast } from '@ionic-native/toast';
import { StorageMulti } from '../../service/storage-multi.service';


//Pipes
import { ObjectToArrayPipe } from '../../pipes/object-to-array/object-to-array';


// Page
import { DetailPage } from '../detail/detail';
import { CategoriesPage } from '../categories/categories';
import { AccountPage } from '../account/account';
import moment from 'moment';

declare var wordpress_url: string;
declare var wordpress_per_page: Number;
declare var Keyboard;

@Component({
	selector: 'page-search',
	templateUrl: 'search.html',
	providers: [Core, ObjectToArrayPipe, StorageMulti]
})
export class SearchPage {

	@ViewChild(TextInput) inputSearch: TextInput;
	@ViewChild('cart') buttonCart;
	DetailPage = DetailPage;
	CategoriesPage = CategoriesPage;
	AccountPage = AccountPage;
	keyword: string;
	products: Object[] = []; attributes: Object[] = [];
	page = 1; sort: string = '-date_created_gmt'; range: Object = { lower: 0, upper: 0 };
	categories: Object[] = [];
	filter: any = { grid: true, open: null, value: {}, valueCustom: {} }; filtering: boolean;
	grid: boolean = true;
	favorite: Object = {};
	trans: Object = {};
	over: boolean; actionCart: Object = [];
	cartArray: Object = {};
	noResuilt: boolean = false;
	quantity: Number = 1;
	data: Object[] = [];
	faded: boolean = false;
	loaddata: boolean = false;
	public date: string = new Date().toISOString();
	public today = moment();

	constructor(
		public http: Http,
		public core: Core,
		public storage: Storage,
		public navCtrl: NavController,
		public navParams: NavParams,
		public Toast: Toast,
		public storageMul: StorageMulti
	) {

		this.search();

		this.loadCategories();

		http.get(wordpress_url + '/wp-json/wooconnector/product/getattribute')
			.subscribe(res => {
				console.log(res.json());
				this.attributes = res.json();
				this.attributes['custom'] = new ObjectToArrayPipe().transform(this.attributes['custom']);
				this.reset();
			});
	}
	ngOnInit() {
		if (this.inputSearch) {
			console.log(this.inputSearch);
			this.inputSearch["clearTextInput"] = (): void => {
				(void 0);
				this.inputSearch._value = '';
				// this.inputSearch.ionChange(this.inputSearch._value);
				this.inputSearch.writeValue(this.inputSearch._value);
				setTimeout(() => { this.inputSearch.setFocus(); }, 100);
			}
		}
	}
	ionViewDidEnter() {
		this.checkCart();
		this.getFavorite();
		this.buttonCart.update();
		// setTimeout(() => { this.inputSearch.setFocus(); }, 100);
	}
	checkCart() {
		this.storage.get('cart').then(val => {
			let cartNew = Object.assign([], val);
			this.cartArray = {};
			cartNew.forEach(productCart => {
				this.cartArray[productCart['id']] = productCart['id'];
				console.log(this.cartArray);
			});
		});
	}
	getFavorite() {
		this.storage.get('favorite').then(val => { if (val) this.favorite = val });
	}
	reset() {
		this.filter['value'] = {};
		this.filter['valueCustom'] = {};
		this.attributes['attributes'].forEach(attr => {
			this.filter['value'][attr['slug']] = {};
		});
		this.attributes['custom'].forEach(attr => {
			this.filter['valueCustom'][attr['slug']] = {};
		});
		this.range = { lower: 0, upper: 0 };
	}
	openCategory() {
		if (this.filter['open'] == 'category') this.filter['open'] = null;
		else this.filter['open'] = 'category';
	}
	openFilter() {
		if (this.filter['open'] == 'filter') this.filter['open'] = null;
		else this.filter['open'] = 'filter';
	}
	openSort() {
		if (this.filter['open'] == 'sort') this.filter['open'] = null;
		else this.filter['open'] = 'sort';
	}
	search() {

		console.log('searching...');

		if (document.URL.indexOf('http') !== 0) {
			Keyboard.hide();
		}
		if (this.filter['open'] == 'filter') this.openFilter();
		this.page = 1;
		this.over = false;
		this.loaddata = true;
		this.getProducts().subscribe(products => {
			if (products && products.length > 0) {
				this.noResuilt = false;
				this.loaddata = false
				this.page++;
				if (this.data) {
					products.forEach(val => {
						this.data.forEach(cart => {
							if (val['id'] == cart['id']) val['onCart'] = true;
						});
					});
				}
				this.products = products;

				console.log(this.products);

				setTimeout(() => {
					this.faded = true;
				}, 100);
			} else {
				this.products = [];
				this.noResuilt = true;
				this.loaddata = false;
			}
		});
	}
	getProducts(): Observable<Object[]> {
		return new Observable(observable => {
			let tmpFilter = [];
			for (var filter in this.filter['value']) {
				let attr = this.filter['value'][filter];
				if (Object.keys(attr).length > 0) for (var option in attr) {
					if (attr[option]) {
						let now = {};
						now['keyattr'] = filter;
						now['valattr'] = option;
						now['type'] = 'attributes';
						tmpFilter.push(now);
					}
				};
			}
			for (var filter in this.filter['valueCustom']) {
				let attr = this.filter['value'][filter];
				if (attr && Object.keys(attr).length > 0) for (var option in attr) {
					if (attr[option]) {
						let now = {};
						now['keyattr'] = filter;
						now['valattr'] = option;
						now['type'] = 'custom';
						tmpFilter.push(now);
					}
				};
			}
			let params: any = {
				'search': this.keyword,
				'post_num_page': this.page,
				'post_per_page': wordpress_per_page,
			}

			if (this.filterCategory) {
				params.post_category = this.filterCategory.id;
			}

			let sortParams = this.core.addSortToSearchParams(params, this.sort);
			if (tmpFilter.length == 0 && !this.range['lower'] && !this.range['upper']) {
				this.http.get(wordpress_url + '/wp-json/wooconnector/product/getproduct', {
					search: this.core.objectToURLParams(params)
				}).subscribe(products => {
					observable.next(products.json());
					observable.complete();
				});
			} else {
				if (tmpFilter.length > 0) params['attribute'] = JSON.stringify(tmpFilter);
				if (this.range['lower'] != 0) params['min_price'] = this.range['lower'];
				if (this.range['upper'] != 0) params['max_price'] = this.range['upper'];
				this.http.get(wordpress_url + '/wp-json/wooconnector/product/getproductbyattribute', {
					search: this.core.objectToURLParams(params)
				}).subscribe(products => {
					observable.next(products.json());
					observable.complete();
				});
			}
		});
	}
	load(infiniteScroll) {
		this.page++;
		this.getProducts().subscribe(products => {
			if (products && products.length > 0) {
				this.products = this.products.concat(products);
			} else this.over = true;
			infiniteScroll.complete();
		});
	}

	filterCategory;
	setCategory(category) {

		if (category) {
			this.filterCategory = category;
		} else {
			this.filterCategory = null;
		}
		this.filter.open = null

		console.log('set category : ' + this.filterCategory);

		this.search();
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

					this.categories = val['all_category'];

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

				this.categories = this.categories.concat(res.json());

				if (res.json() && res.json().length == 100) {

					this.cat_num_page++;
					loadCategories();
				} else {

					this.storage.set('all_category', this.categories);
					this.storage.set('last_sync_all_category', this.date);
				}
			});
		};
		loadCategories();

	}

	changeFavorite(product: Object) {
		if (this.favorite[product["id"]]) {
			delete this.favorite[product["id"]];
			this.storage.set('favorite', this.favorite);
		} else {
			let data: any = {
				id: product["id"],
				name: product["name"],
				regular_price: product["regular_price"],
				sale_price: product["sale_price"],
				price: product["price"],
				on_sale: product["on_sale"],
				price_html: product["price_html"],
				type: product["type"]
			};
			if (product["modernshop_images"]) data['images'] = product["modernshop_images"][0].modern_square;
			this.favorite[product["id"]] = data;
			this.storage.set('favorite', this.favorite);
		}
	}
	addtoCart(detail: any) {
		if (!detail['in_stock']) {
			this.Toast.showShortBottom("Out of Stock").subscribe(
				toast => { },
				error => { console.log(error); }
			);
			return;
		}
		let data: any = {};
		let idCart = detail["id"];
		data.idCart = idCart;
		data.id = detail["id"];
		data.name = detail["name"];
		if (detail["wooconnector_crop_images"])
			data.images = detail["wooconnector_crop_images"][0].wooconnector_medium;
		data.regular_price = detail["regular_price"];
		data.sale_price = detail["sale_price"];
		data.price = detail["price"];
		data.quantity = this.quantity;
		data.sold_individually = detail['sold_individually'];
		this.storage.get('cart').then((val) => {
			let individually: boolean = false;
			if (!val) val = {};
			if (!val[idCart]) val[idCart] = data;
			else {
				if (!detail['sold_individually']) val[idCart].quantity += data.quantity;
				else individually = true;
			}
			if (individually) {
				this.Toast.showShortBottom(this.trans['individually']['before'] + detail['name'] + this.trans['individually']['after']).subscribe(
					toast => { },
					error => { console.log(error); }
				);
			} else this.storage.set('cart', val).then(() => {
				this.checkCart();
				this.buttonCart.update();
				if (!detail['in_stock'] && detail['backorders'] == 'notify') {
					this.Toast.showShortBottom(this.trans["addOut"]).subscribe(
						toast => { },
						error => { console.log(error); }
					);
				} else {
					this.Toast.showShortBottom(this.trans["add"]).subscribe(
						toast => { },
						error => { console.log(error); }
					);
				}
			});
		});
	}
	onSwipeContent(e) {
		if (e['deltaX'] < -150 || e['deltaX'] > 150) {
			if (e['deltaX'] < 0) this.navCtrl.push(this.AccountPage);
			else this.navCtrl.push(this.CategoriesPage);
		}
	}

	getFilter() {

		this.filter.open = null;
		this.search();
	}

}
