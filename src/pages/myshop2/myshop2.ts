import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, AlertController } from 'ionic-angular';
import { StorageMulti } from '../../service/storage-multi.service';
import { Core } from '../../service/core.service';
import { Device } from '@ionic-native/device';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';
import { Storage } from '@ionic/storage';
import { Toast } from '@ionic-native/toast';

import { VendorUpdatePage } from '../../pages/vendor-update/vendor-update';
import { BrowserPage } from '../../pages/browser/browser';
import { CreateProductPage } from '../../pages/create-product/create-product';
import { VendorRegisterPage } from '../../pages/vendor-register/vendor-register';

@Component({
  selector: 'page-myshop2',
  templateUrl: 'myshop2.html',
  providers: [StorageMulti, Device, Core]
})
export class Myshop2Page {

  BrowserPage = BrowserPage;

  userStatus = 'logoff';
  shopStatus = 'loading';
  shop: any = {};
  user: any = {};
  login: any = {};
  customer: any = {};
  segment = 'shop';
  products = [];
  productLoaded = false;

  constructor(
    public navCtrl: NavController,
    public navParams: NavParams,
    public storage: Storage,
    public storageMul: StorageMulti,
    public WP: WoocommerceProvider,
    public toastCtrl: ToastController,
    public alertCtrl: AlertController,
    public core: Core,
    public Toast: Toast

  ) {

  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad Myshop2Page');
  }

  ionViewDidEnter() {
    this.checkLogin();
  }

  checkLogin() {

    this.storageMul.get(['login', 'user', 'customer', 'shop']).then(val => {

      if (val["login"] && val["user"]) {

        this.user = val["user"];
        this.login = val["login"];

        console.log('user');
        console.log(this.user);

        this.userStatus = 'login';
        this.checkShop(val["user"].ID);

        if (!val["shop"]) {

          this.shopStatus = 'loading';

        }

      } else {
        this.userStatus = 'logoff';
        this.shopStatus = 'none';
        this.segment = 'shop';
      }



    });

  }

  checkShop(id) {

    console.log('checking shop...');

    let checkingShop = this.WP.get({
      wcmc: false,
      method: 'GET',
      api: 'customers/' + id
    });

    checkingShop.subscribe(data => {

      try {

        if (data.json()) {

          let customer = data.json();
          console.log(customer);

          if (customer.id && customer.role) {
            this.customer = customer;
            this.storage.set('customer', customer);
            this.setShopStatus(customer);
            this.setShop(customer);
          }

        }

      } catch (e) {
        console.log(e);
      }

    }, err => {

      console.log('error oi');

    });

  }

  setShop(customer) {

    console.log('setting shop...');

    let meta_data = customer.meta_data;
    let shop: any = {};

    if (!this.getMeta(meta_data, '_vendor_page_title')) {
      return;
    }

    shop.title = this.getMeta(meta_data, '_vendor_page_title');
    shop.slug = this.getMeta(meta_data, '_vendor_page_slug');
    shop.description = this.getMeta(meta_data, '_vendor_description');
    shop.address_1 = this.getMeta(meta_data, '_vendor_address_1');
    shop.address_2 = this.getMeta(meta_data, '_vendor_address_2');
    shop.city = this.getMeta(meta_data, '_vendor_city');
    shop.postcode = this.getMeta(meta_data, '_vendor_postcode');
    shop.state = this.getMeta(meta_data, '_vendor_state');
    shop.country = this.getMeta(meta_data, '_vendor_country');
    shop.phone = this.getMeta(meta_data, '_vendor_phone');
    shop.image = this.getMeta(meta_data, '_vendor_image');
    shop.banner = this.getMeta(meta_data, '_vendor_banner');
    shop.message_to_buyers = this.getMeta(meta_data, '_vendor_message_to_buyers');

    if (this.getMeta(meta_data, '_vendor_turn_off') == 'Enable') {
      shop.activated = false;
    } else {
      shop.activated = true;
    }

    if (!shop.image) {
      shop.image = 'assets/images/person.png';
    }

    if (!shop.banner) {
      shop.banner = 'assets/images/loading-wave.gif';
    }

    this.shop = shop;
    this.storage.set('shop', shop);

  }

  getMeta(element, key) {

    let temp = element.find(function (element) {
      return element.key == key;
    });

    if (temp) {
      return temp.value;
    } else {
      return '';
    }

  }

  setShopStatus(customer) {

    console.log('setting status...');

    let status = customer.role;
    console.log(status);

    if (!status) {
      this.shopStatus = 'unregistered';
    }

    if (status == 'customer') {
      this.shopStatus = 'unregistered';
    }

    if (status == 'dc_vendor') {

      if (this.getMeta(customer.meta_data, '_vendor_turn_off') == 'Enable') {
        this.shopStatus = 'suspended';
        this.segment = 'shop';
      } else {
        this.shopStatus = 'registered';
      }

    }

    if (status == 'dc_pending_vendor') {
      this.shopStatus = 'pending';
    }

    if (status == 'dc_rejected_vendor') {
      this.shopStatus = 'rejected';
    }

    console.log('status: ' + this.shopStatus);

  }

  accountPage() {
    this.navCtrl.parent.select(4);
  }

  registerPage(type) {

    this.navCtrl.push(VendorRegisterPage,
      {
        username: this.customer.username,
        callback: this.registerCallback,
        type: type
      });

  }

  storeFrontPage() {

    this.navCtrl.push(VendorUpdatePage,
      {
        vendor: this.shop,
        callback: this.updateCallback
      });
  }

  createProductPage() {
    this.navCtrl.push(CreateProductPage, {
      vendor: this.customer.id,
      callback: this.productCallback
    });
  }

  updateCallback = data => {
    return new Promise((resolve, reject) => {

      console.log(data);
      this.storage.remove('customer');
      this.checkShop(this.user.ID);

      resolve();
    });
  };

  registerCallback = data => {
    return new Promise((resolve, reject) => {

      this.checkLogin();
      this.shopStatus = 'loading';
      this.productLoaded = false;

      resolve();
    });
  };

  segmentChanged(event) {
    console.log(event._value);

    if (event._value == 'product') {
      this.getProduct(this.user.ID);
    } else {
    }
  }

  getProduct(id, refresher = null) {

    console.log('enter get product');

    let params = {
      vendor: id
    };

    let getProduct = this.WP.get({
      wcmc: false,
      method: 'GET',
      api: 'products',
      param: params
    });

    getProduct.subscribe(data => {

      this.productLoaded = true;

      console.log(data.json());
      if (data.json()) {

        this.products = data.json();

      }

      if (refresher) {
        refresher.complete();
      }

    }, err => {

      if (refresher) {
        refresher.complete();
      }

      console.log('error oi');

    });

  }

  doRefresh(refresher) {

    this.checkLogin();
    this.shopStatus = 'loading';
    this.productLoaded = false;

    if (this.segment == 'product') {

      refresher.complete();
      this.getProduct(this.user.ID);

    } else {

      refresher.complete();

    }


  }

  refreshPage() {

    this.checkLogin();
    this.shopStatus = 'loading';
    this.productLoaded = false;

  }

  updateProduct(product) {

    this.navCtrl.push(CreateProductPage, {
      vendor: this.customer.id,
      product: product,
      callback: this.productCallback
    });

  }

  deleteProduct(product) {

    const confirm = this.alertCtrl.create({
      title: 'Delete ' + product.name + '?',
      message: 'This will permanently delete the product?',
      buttons: [
        {
          text: 'Cancel',
          handler: () => {
            console.log('Disagree clicked');
          }
        },
        {
          text: 'Delete',
          handler: () => {
            this.core.showLoading();
            console.log('Agree clicked');

            let wooDelete = this.WP.get({
              wcmc: false,
              method: 'DELETE',
              api: 'products/' + product.id,
              param: {
                // force: 'true'
              }
            });

            wooDelete.subscribe(data => {

              if (data.json()) {

                this.Toast.showShortBottom('Product deleted').subscribe(
                  toast => { },
                  error => { console.log(error); }
                );

              } else {

                this.Toast.showShortBottom('Some error occured').subscribe(
                  toast => { },
                  error => { console.log(error); }
                );

              }

              this.deleteLocalProduct(product.id);
              this.Toast.showShortBottom('Product deleted');
              this.core.hideLoading();

            }, err => {

              this.Toast.showShortBottom('Some error occured').subscribe(
                toast => { },
                error => { console.log(error); }
              );

              this.core.hideLoading();
              console.log('error oi');

            });

          }
        }
      ]
    });
    confirm.present();
  }

  deleteLocalProduct(id) {
    this.products.forEach((item, index) => {
      if (item.id === id) this.products.splice(index, 1);
    });
  }

  productCallback = data => {

    this.getProduct(this.user.ID);

    return new Promise((resolve, reject) => {

      this.getProduct(this.user.ID);

      resolve();
    });

  }

}
