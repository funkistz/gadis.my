import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, AlertController } from 'ionic-angular';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { DomSanitizer } from '@angular/platform-browser';
import { Toast } from '@ionic-native/toast';
import { Storage } from '@ionic/storage';
import { TranslateService } from '../../module/ng2-translate';
import { StorageMulti } from '../../service/storage-multi.service';
import { OneSignal } from '@ionic-native/onesignal';
import { InAppBrowser } from '@ionic-native/in-app-browser';
import { Config } from '../../service/config.service';
import { Core } from '../../service/core.service';
import { Device } from '@ionic-native/device';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';

import { VendorRegisterPage } from '../../pages/vendor-register/vendor-register';
import { BrowserPage } from '../../pages/browser/browser';
import { CreateProductPage } from '../../pages/create-product/create-product';
import { VendorUpdatePage } from '../../pages/vendor-update/vendor-update';

@Component({
  selector: 'page-myshop',
  templateUrl: 'myshop.html',
  providers: [StorageMulti, Device, Core]
})
export class MyshopPage {

  VendorRegisterPage = VendorRegisterPage;
  BrowserPage = BrowserPage;
  segment = 'shop';
  isLogin: boolean;
  data: any = {};
  info: any = '{}';
  isShopLoaded = false;
  shop: any;
  WooCommerce: any;
  formEdit: FormGroup;

  products = [];
  externalLink = 'https://www.gadis.my/redirectVendor.php';
  safeURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink);
  storeFrontURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink);
  loaded = false;
  first = false;
  shopReady = false;

  user: any;
  userLoaded = false;

  login: any;
  customer: any;
  customerLoaded = false;
  role: string;

  debug: string;

  //shop info

  constructor(
    public navCtrl: NavController,
    public navParams: NavParams,
    public storage: Storage,
    public storageMul: StorageMulti,
    public toastCtrl: ToastController,
    public WP: WoocommerceProvider,
    public formBuilder: FormBuilder,
    public core: Core,
    public sanitizer: DomSanitizer,
    public alertCtrl: AlertController,
    public Toast: Toast
  ) {

    this.loaded = false;
    this.storage.remove('customer');

    this.isShopLoaded = false;

  }

  ionViewDidEnter() {
    this.loaded = false;
    this.getData();
  }


  getData() {

    this.userLoaded = false;
    this.user = null;
    this.shop = null;
    this.customer = null;
    this.customerLoaded = false;

    console.log('enter get data');

    this.storageMul.get(['login', 'user', 'customer', 'shop']).then(val => {

      this.userLoaded = true;

      if (val["login"]) {

        console.log(val["login"]);
        this.login = val["login"];

      }
      if (val["user"]) {

        console.log(val["user"]);
        this.user = val["user"];

        if (val["customer"]) {

          console.log(val["customer"]);
          this.customerLoaded = true;
          this.customer = val["customer"];
          this.role = val["customer"].role;
          this.setShop(this.customer);
          console.log('role : ' + this.role);

          this.loaded = true;

        } else {

          this.checkShop(val["user"].ID);

        }

        if (val["shop"]) {

          this.shop = val["shop"];

        }

      } else {
        this.loaded = true;
      }

    });

  }

  checkingShop;
  checkShop(id) {

    console.log('check shop');

    this.customerLoaded = false;

    this.checkingShop = this.WP.get({
      wcmc: false,
      method: 'GET',
      api: 'customers/' + id
    });

    this.checkingShop.subscribe(data => {

      this.customerLoaded = true;

      try {

        if (data.json()) {

          let customer = data.json();
          console.log(customer);

          if (customer.id && customer.role) {
            this.storage.set('customer', customer);
            this.customer = customer;
            this.role = customer.role;
            this.setShop(this.customer);
            this.getProduct(this.user.ID);

            this.loaded = true;
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

    if (!shop.image) {
      shop.image = 'assets/images/person.png';
    }

    if (!shop.banner) {
      shop.banner = 'assets/images/loading-wave.gif';
    }

    this.safeURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink + '?username=' + this.customer.username + '&task=' + 'vendor-orders');

    this.shop = shop;
    this.storage.set('shop', shop);
    console.log(shop);

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

  customerRole(customer) {

    return customer.role;

  }

  accountPage() {
    // this.navCtrl.push(this.CategoriesPage);
    this.navCtrl.parent.select(4);
  }

  createProductPage() {
    this.navCtrl.push(CreateProductPage, {
      vendor: this.customer.id,
      callback: this.productCallback
    });
  }

  updateInfo() {

    this.core.showLoading();

    let temp = this.formEdit.value;
    let params_temp =
    {
      "_vendor_message_to_buyers": temp.message_to_buyers,
      "_vendor_page_title": temp.name,
      // "_vendor_page_slug": temp.slug,
      "_vendor_description": temp.description,
      "_vendor_address_1": temp.address_1,
      "_vendor_address_2": temp.address_2,
      "_vendor_city": temp.city,
      "_vendor_state": temp.state,
      "_vendor_country": temp.country,
      "_vendor_postcode": temp.postcode,
      "_vendor_phone": temp.phone,
      // "_vendor_fb_profile": temp.facebook,
      // "_vendor_twitter_profile": temp.twitter,
      // "_vendor_google_plus_profile": temp.google_plus,
      // "_vendor_linkdin_profile": temp.linkedin,
      // "_vendor_youtube": temp.youtube,
      // "_vendor_instagram": temp.instagram
    };

    let meta = [];

    Object.keys(params_temp).forEach(key => {

      meta.push({
        "key": key,
        "value": params_temp[key]
      });

    });

    let params = {
      "meta_data": JSON.stringify(meta)
    };

    console.log(params);

    this.WooCommerce = this.WP.get({
      wcmc: true,
      method: 'PUT',
      api: 'vendors/' + this.customer.id,
      param: params
    });

    this.WooCommerce.subscribe(response => {

      console.log(response);

      if (response) {

        if (response.json().id) {

          const toast = this.toastCtrl.create({
            message: 'Shop updated',
            duration: 3000
          });
          toast.present();

        }

      }

      this.core.hideLoading();

    }, err => {

      const toast = this.toastCtrl.create({
        message: 'Some error occured',
        duration: 3000
      });
      toast.present();
      console.log('error oi');
      this.core.hideLoading();

    });

  }

  presentToast(message) {
    const toast = this.toastCtrl.create({
      message: message,
      duration: 3000
    });
    toast.present();
  }

  wooProduct: any;
  productLoaded = false;

  getProduct(id, refresher = null) {

    this.productLoaded = false;
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

      console.log(data.json());
      if (data.json()) {

        this.products = data.json();
        this.productLoaded = true;

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

  updateImage() {

    this.navCtrl.push(BrowserPage, {
      task: 'storefront'
    });

  }

  shopStyle = "visible";
  orderStyle = "hidden";
  fab = false;
  orderHeight = '0px';

  segmentChanged(event) {
    console.log(event._value);

    if (event._value == 'product') {
      this.fab = true;
    } else {
      this.fab = false;
    }

    if (event._value == 'shop') {
      console.log('shop visible');
      this.shopStyle = "visible";
      this.orderStyle = "hidden";
      this.orderHeight = '0px'
    } else if (event._value == 'order') {
      console.log('order visible');
      this.shopStyle = "'hidden";
      this.orderStyle = "visible";
      this.orderHeight = '100%;'
    } else {
      console.log('other visible');
      this.shopStyle = "hidden";
      this.orderStyle = "hidden";
      this.orderHeight = '0px'
    }
  }

  dismissLoading() {
    this.loaded = true;
  }

  doRefresh(refresher) {

    if (this.segment == 'product') {

      refresher.complete();
      this.getProduct(this.user.ID);

    } else {

      this.refreshPage();
      refresher.complete();

    }


  }

  refreshPage() {

    this.first = false;
    this.loaded = false;
    this.shop = {
      shop: {
        banner: "assets/images/account-bg.png",
        image: "assets/images/person.png"
      }
    };
    this.getData();

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

  storeFrontPage() {

    this.navCtrl.push(VendorUpdatePage,
      {
        vendor: this.shop,
        callback: this.updateCallback
      });
  }

  registerPage() {

    this.navCtrl.push(VendorRegisterPage,
      {
        username: this.customer.username,
        callback: this.registerCallback
      });

  }

  productCallback = data => {

    this.getProduct(this.user.ID);

    return new Promise((resolve, reject) => {

      this.getProduct(this.user.ID);

      resolve();
    });

  }

  updateCallback = data => {
    return new Promise((resolve, reject) => {

      console.log(data);
      this.storage.remove('customer');
      this.first = false;
      this.getData();

      resolve();
    });
  };

  registerCallback = data => {
    return new Promise((resolve, reject) => {

      console.log(data);
      this.first = false;
      this.getData();
      const toast = this.toastCtrl.create({
        message: 'Sucessfully Registered',
        duration: 3000
      });
      toast.present();

      resolve();
    });
  };

}
