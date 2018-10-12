import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, AlertController } from 'ionic-angular';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { DomSanitizer } from '@angular/platform-browser';

import { Storage } from '@ionic/storage';
import { TranslateService } from '../../module/ng2-translate';
import { StorageMulti } from '../../service/storage-multi.service';
import { OneSignal } from '@ionic-native/onesignal';
import { InAppBrowser } from '@ionic-native/in-app-browser';
import { Config } from '../../service/config.service';
import { Core } from '../../service/core.service';
import { Device } from '@ionic-native/device';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';
import { CreateProductPage } from '../../pages/create-product/create-product';
import { BrowserPage } from '../../pages/browser/browser';
import { Toast } from '@ionic-native/toast';

@IonicPage()
@Component({
  selector: 'page-myshop',
  templateUrl: 'myshop.html',
  providers: [StorageMulti, Device, Core]
})
export class MyshopPage {

  BrowserPage = BrowserPage;
  segment = 'shop';
  isLogin: boolean;
  data: any = {};
  info: any = '{}';
  isShopLoaded = false;
  shop: any = {
    shop: {
      banner: "assets/images/account-bg.png",
      image: "assets/images/person.png"
    }
  };
  WooCommerce: any;
  formEdit: FormGroup;

  products = [];
  externalLink = 'https://www.gadis.my/redirectVendor.php';
  safeURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink);
  storeFrontURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink);
  loaded = false;
  first = false;
  shopReady = false;

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

    // this.formEdit = formBuilder.group({
    //   name: ['', Validators.compose([Validators.maxLength(25), Validators.required])],
    //   slug: ['', Validators.compose([Validators.maxLength(25), Validators.required])],
    //   description: ['', Validators.compose([Validators.maxLength(255), Validators.required])],
    //   message_to_buyers: ['', Validators.compose([Validators.maxLength(255)])],
    //   phone: ['', Validators.compose([Validators.maxLength(13)])],
    //   email: ['', Validators.compose([Validators.maxLength(55), Validators.required])],
    //   address_1: ['', Validators.compose([Validators.maxLength(25)])],
    //   address_2: ['', Validators.compose([Validators.maxLength(25)])],
    //   city: ['', Validators.compose([Validators.maxLength(25)])],
    //   state: ['', Validators.compose([Validators.maxLength(25)])],
    //   country: ['', Validators.compose([Validators.maxLength(25)])],
    //   postcode: ['', Validators.compose([Validators.maxLength(25)])],
    //   facebook: ['', Validators.compose([Validators.maxLength(40)])],
    //   twitter: ['', Validators.compose([Validators.maxLength(40)])],
    //   google_plus: ['', Validators.compose([Validators.maxLength(40)])],
    //   linkedin: ['', Validators.compose([Validators.maxLength(40)])],
    //   youtube: ['', Validators.compose([Validators.maxLength(40)])],
    //   instagram: ['', Validators.compose([Validators.maxLength(40)])]
    // });

    this.isShopLoaded = false;
    this.shop = {
      shop: {
        banner: "assets/images/account-bg.png",
        image: "assets/images/person.png"
      }
    };

  }

  ionViewDidEnter() {
    this.loaded = false;
    this.getData();
  }

  getData() {

    console.log('enter get data');

    this.storageMul.get(['login', 'user']).then(val => {

      console.log('user');
      console.log(val);

      if (val) {

        if (val["user"]) {

          if (this.data["user"]) {

            if (this.data["user"].ID != val["user"]['mobiconnector_info'].ID) {
              this.first = false;
              this.shop = {};
              this.shop = {
                shop: {
                  banner: "assets/images/account-bg.png",
                  image: "assets/images/person.png"
                }
              };
            }

          }

          this.data["user"] = val["user"]['mobiconnector_info'];
        }
        if (val["login"] && val["login"]["token"]) {
          this.isLogin = true;
          this.data["login"] = val["login"];

          if (!this.first) {
            this.checkShop(this.data["user"].ID);
          } else {
            this.loaded = true;
          }

        } else {
          this.isLogin = false;
          this.first = false;

          console.log('logout');
          this.shop = {};
          this.shop = {
            shop: {
              banner: "assets/images/account-bg.png",
              image: "assets/images/person.png"
            }
          };
          this.isLogin = false;
          this.loaded = true;

        }

        this.info = JSON.stringify(val);
        console.log(this.data["login"]);
        // this.presentToast(val);

      }
    });
  }

  checkShop(id) {

    this.shopReady = false;

    console.log('enter check shop');

    this.storeFrontURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink + '?username=' + this.data["login"].user_display_name + '&task=' + 'storefront');
    this.safeURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink + '?username=' + this.data["login"].user_display_name + '&task=' + 'vendor-orders');

    this.WooCommerce = this.WP.get({
      wcmc: true,
      method: 'GET',
      api: 'vendors/' + id
    });

    this.WooCommerce.subscribe(data => {

      this.isShopLoaded = true;
      this.shopReady = true;
      console.log(data);

      try {

        if (data.json()) {
          console.log('shop');
          let shop = data.json();
          console.log(shop);

          if (shop.id) {

            this.first = true;
            this.getProduct(this.data["user"].ID);

            if (!shop.shop.banner) {
              shop.shop.banner = "assets/images/account-bg.png";
            }

            if (!shop.shop.image) {
              shop.shop.image = "assets/images/person.png";
            }

            this.shop = shop;

            // this.formEdit.controls['name'].setValue(shop.shop.title);
            // this.formEdit.controls['slug'].setValue(shop.shop.slug);
            // this.formEdit.controls['description'].setValue(shop.shop.description);
            // this.formEdit.controls['message_to_buyers'].setValue(shop.message_to_buyers);
            // this.formEdit.controls['phone'].setValue(shop.address.phone);
            // this.formEdit.controls['email'].setValue(shop.login);

            // this.formEdit.controls['address_1'].setValue(shop.address.address_1);
            // this.formEdit.controls['address_2'].setValue(shop.address.address_2);
            // this.formEdit.controls['city'].setValue(shop.address.city);
            // this.formEdit.controls['country'].setValue(shop.address.country);
            // this.formEdit.controls['postcode'].setValue(shop.address.postcode);
            // this.formEdit.controls['state'].setValue(shop.address.state);

            // this.formEdit.controls['facebook'].setValue(shop.social.facebook);
            // this.formEdit.controls['twitter'].setValue(shop.social.twitter);
            // this.formEdit.controls['google_plus'].setValue(shop.social.google_plus);
            // this.formEdit.controls['linkedin'].setValue(shop.social.linkdin);
            // this.formEdit.controls['youtube'].setValue(shop.social.youtube);
            // this.formEdit.controls['instagram'].setValue(shop.social.instagram);

          }

        }
      } catch (e) {
        this.shopReady = true;
        console.log('error try check shop');
        return false;
      }

    }, err => {

      console.log('error oi');

    });

  }

  accountPage() {
    // this.navCtrl.push(this.CategoriesPage);
    this.navCtrl.parent.select(5);
  }

  createProductPage() {
    this.navCtrl.push(BrowserPage, {
      task: 'add-product'
    });
  }

  updateInfo() {

    this.core.showLoading();

    let temp = this.formEdit.value;
    let params_temp =
    {
      "_vendor_message_to_buyers": temp.message_to_buyers,
      "_vendor_page_title": temp.name,
      "_vendor_page_slug": temp.slug,
      "_vendor_description": temp.description,
      "_vendor_address_1": temp.address_1,
      "_vendor_address_2": temp.address_2,
      "_vendor_city": temp.city,
      "_vendor_state": temp.state,
      "_vendor_country": temp.country,
      "_vendor_postcode": temp.postcode,
      "_vendor_phone": temp.phone,
      "_vendor_fb_profile": temp.facebook,
      "_vendor_twitter_profile": temp.twitter,
      "_vendor_google_plus_profile": temp.google_plus,
      "_vendor_linkdin_profile": temp.linkedin,
      "_vendor_youtube": temp.youtube,
      "_vendor_instagram": temp.instagram
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
      wcmc: false,
      method: 'PUT',
      api: 'customers/' + this.shop.id,
      param: params
    });

    this.WooCommerce.subscribe(response => {

      if (response) {

        console.log(response.json());


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
        message: err,
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

    this.wooProduct = this.WP.get({
      wcmc: false,
      method: 'GET',
      api: 'products',
      param: params
    });

    this.wooProduct.subscribe(data => {

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

  segmentChanged(event) {
    console.log(event._value);

    if (event._value == 'product') {
      this.fab = true;
    } else {
      this.fab = false;
    }

    if (event._value == 'shop') {
      this.shopStyle = "visible";
      this.orderStyle = "hidden";
    } else if (event._value == 'order') {
      this.shopStyle = "'hidden";
      this.orderStyle = "visible";
    } else {
      this.shopStyle = "hidden";
      this.orderStyle = "hidden";
    }
  }

  dismissLoading() {
    this.loaded = true;
  }

  doRefresh(refresher) {

    if (this.segment == 'product') {

      this.getProduct(this.data["user"].ID, refresher);

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

              console.log(data);
              console.log(data.json());
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

              this.getProduct(this.data["user"].ID);
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

}
