import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController } from 'ionic-angular';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { Storage } from '@ionic/storage';
import { TranslateService } from '../../module/ng2-translate';
import { StorageMulti } from '../../service/storage-multi.service';
import { OneSignal } from '@ionic-native/onesignal';
import { InAppBrowser } from '@ionic-native/in-app-browser';
import { Config } from '../../service/config.service';
import { Core } from '../../service/core.service';
import { Device } from '@ionic-native/device';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';
import { Http } from '@angular/http';

@Component({
  selector: 'page-create-product',
  templateUrl: 'create-product.html',
  providers: [StorageMulti, Device, Core]
})
export class CreateProductPage {

  WooCommerce: any;
  formEdit: FormGroup;
  categories = [];

  constructor(
    public navCtrl: NavController,
    public navParams: NavParams,
    public storage: Storage,
    public storageMul: StorageMulti,
    public toastCtrl: ToastController,
    public WP: WoocommerceProvider,
    public formBuilder: FormBuilder,
    public core: Core,
    public http: Http,
    private theInAppBrowser: InAppBrowser
  ) {

    let target = "_blank";
    this.theInAppBrowser.create('https://www.gadis.my/redirectVendor.php', target);

    this.formEdit = formBuilder.group({
      brand: ['', Validators.compose([Validators.maxLength(25), Validators.required])],
      name: ['', Validators.compose([Validators.maxLength(25), Validators.required])],
      regular_price: ['', Validators.compose([Validators.maxLength(255), Validators.required])],
      sale_price: ['', Validators.compose([Validators.maxLength(255), Validators.required])],
      short_description: ['', Validators.compose([Validators.maxLength(255), Validators.required])],
      categories: ['', Validators.compose([Validators.maxLength(255), Validators.required])]
    });

    this.getCategories();

  }

  getCategories() {

    let params = { cat_num_page: 1, cat_per_page: 300 };

    this.http.get('http://www.gadis.my/wp-json/wooconnector/product/getcategories', {
      search: this.core.objectToURLParams(params)
    }).subscribe(res => {

      if (res.json()) {
        this.categories = res.json();
        console.log(this.categories);
      } else {
      }
    });

  }

  addProduct(id) {

    let temp = this.formEdit.value;

    let params = {
      vandor: id,
      brand: temp.brand,
      name: temp.name,
      regular_price: temp.regular_price,
      sale_price: temp.sale_price,
      short_description: temp.short_description,
      categories: [temp.categories],
    }

    this.WooCommerce = this.WP.get({
      wcmc: false,
      method: 'POST',
      api: 'products',
      param: params
    });

    this.WooCommerce.subscribe(response => {

      if (response) {

        console.log(response);


        if (response.json().id) {

          const toast = this.toastCtrl.create({
            message: 'Product Created',
            duration: 3000
          });
          toast.present();

        }

      }
    });
  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad CreateProductPage');
  }

}
