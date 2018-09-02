import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { Http } from '@angular/http';


// Custom
import { Core } from '../../service/core.service';

// Page
import { DetailCategoryPage } from '../detail-category/detail-category';
import { SearchPage } from '../search/search';


declare var wordpress_url:string;

@Component({
  selector: 'page-categories',
  templateUrl: 'categories.html',
  providers: [Core]
})
export class CategoriesPage {

  @ViewChild('cart') buttonCart;
	DetailCategoryPage = DetailCategoryPage;
	SearchPage = SearchPage;
	parents:Object[] = [];
	id:Number;
	noResuilt:boolean = false;
	faded:boolean = false;
	loaddata: boolean = false;
	constructor(
		public http: Http,
		public core: Core,
		public navCtrl: NavController
	){
		let params = {cat_num_page:1, cat_per_page:100, parent: '0'};
		let loadCategories = () => {
			http.get(wordpress_url+'/wp-json/wooconnector/product/getcategories', {
				search:core.objectToURLParams(params)
			}).subscribe(res => {
				this.loaddata = true;
				this.parents = this.parents.concat(res.json());
				setTimeout(() => {
					this.faded = true;
				},100);
				if(res.json() && res.json().length == 100){
					this.noResuilt = false;
					params.cat_num_page++;
					loadCategories();
				} else {
					this.loaddata = true;
					this.noResuilt = true;
				}
			});
		};
		loadCategories();
	}
	ionViewDidEnter(){
		this.buttonCart.update();
	}
	onSwipeContent(e){
		if(e['deltaX'] < -150 || e['deltaX'] > 150){
			if(e['deltaX'] < 0) this.navCtrl.push(this.SearchPage);
			else this.navCtrl.popToRoot();
		}
	}

}
