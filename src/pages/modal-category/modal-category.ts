import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ViewController } from 'ionic-angular';
import { Http } from '@angular/http';
import { Core } from '../../service/core.service';
import { Storage } from '@ionic/storage';
import { StorageMulti } from '../../service/storage-multi.service';
import moment from 'moment';

@Component({
  selector: 'page-modal-category',
  templateUrl: 'modal-category.html',
  providers: [Core, StorageMulti]
})
export class ModalCategoryPage {

  categories: any = [];
  tempCategories: any = [];

  loading = true;
  public today = moment();
  public date: string = new Date().toISOString();

  constructor(
    public navCtrl: NavController,
    public navParams: NavParams,
    public viewCtrl: ViewController,
    public http: Http,
    public core: Core,
    public storage: Storage,
    public storageMul: StorageMulti
  ) {

    this.storageMul.get(['last_sync_all_category', 'all_category']).then(val => {

      if (val['last_sync_all_category'] && val['all_category']) {

        if (!this.today.isSame(new Date(val['last_sync_all_category']), "day")) {

          console.log('not today all category');
          this.getCategories();

        } else {

          this.tempCategories = val['all_category'];
          this.categories = val['all_category'];
          this.loading = false;
        }

      } else {

        this.getCategories();

      }

    });

  }

  ionViewDidEnter() {
    this.today = moment();
  }

  search(ev: any) {

    if (this.loading) {
      return;
    }

    this.categories = this.tempCategories;

    const val = ev.target.value;

    // if the value is an empty string don't filter the items
    if (val && val.trim() != '') {
      this.categories = this.categories.filter((item) => {
        return (item.name.toLowerCase().indexOf(val.toLowerCase()) > -1);
      })
    }

  }

  getCategories() {

    this.loading = true;
    this.categories = [];

    console.log('get categories');

    let params = { cat_num_page: 1, cat_per_page: 300 };
    this.http.get('https://www.gadis.my/wp-json/wooconnector/product/getcategories', {
      search: this.core.objectToURLParams(params)
    }).subscribe(res => {

      this.loading = false;

      if (res.json()) {

        this.storage.set('last_sync_all_category', this.date);
        this.storage.set('all_category', res.json());
        this.tempCategories = res.json();
        this.categories = res.json();
        console.log(this.categories);
      } else {

        console.log(res);

      }
    });

  }

  selectCategory(category) {
    this.viewCtrl.dismiss(category);
  }

}
