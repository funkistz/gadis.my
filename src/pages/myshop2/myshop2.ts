import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, AlertController, LoadingController } from 'ionic-angular';
import { StorageMulti } from '../../service/storage-multi.service';
import { Core } from '../../service/core.service';
import { Device } from '@ionic-native/device';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';
import { Storage } from '@ionic/storage';
import { Toast } from '@ionic-native/toast';
import { Http, Headers } from '@angular/http';

import { VendorUpdatePage } from '../../pages/vendor-update/vendor-update';
import { BrowserPage } from '../../pages/browser/browser';
import { CreateProductPage } from '../../pages/create-product/create-product';
import { VendorRegisterPage } from '../../pages/vendor-register/vendor-register';
import { DetailOrderVendorPage } from '../detail-order-vendor/detail-order-vendor';
import { VendorOrdersPage } from '../vendor-orders/vendor-orders';
import { VendorWithdrawalPage } from '../vendor-withdrawal/vendor-withdrawal';
import { LoginPage } from '../login/login';

declare var wordpress_url: string;
declare var date_format: string;

@Component({
  selector: 'page-myshop2',
  templateUrl: 'myshop2.html',
  providers: [StorageMulti, Device, Core]
})
export class Myshop2Page {

  BrowserPage = BrowserPage;
  DetailOrderPage = DetailOrderVendorPage;
  VendorOrdersPage = VendorOrdersPage;
  VendorWithdrawalPage = VendorWithdrawalPage;
  LoginPage = LoginPage;

  userStatus = 'logoff';
  shopStatus = 'loading';
  shop: any = {};
  user: any = {};
  login: any = {};
  customer: any = {};
  segment = 'dashboard';
  products = [];
  productLoaded = false;
  orders = [];
  orderLoaded = false;
  withdrawal: any = {};
  withdrawLoaded = false;
  loader: any;

  constructor(
    public navCtrl: NavController,
    public navParams: NavParams,
    public storage: Storage,
    public storageMul: StorageMulti,
    public WP: WoocommerceProvider,
    public toastCtrl: ToastController,
    public alertCtrl: AlertController,
    public core: Core,
    public Toast: Toast,
    public http: Http,
    public loadingCtrl: LoadingController
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
        this.segment = 'dashboard';
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
            this.getWithdrawal(customer.id);
            this.getOrder(customer.id);
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

    //billing
    shop.account_type = this.getMeta(meta_data, '_vendor_bank_account_type');
    shop.payment_mode = this.getMeta(meta_data, '_vendor_payment_mode');
    shop.bank_account_number = this.getMeta(meta_data, '_vendor_bank_account_number');
    shop.bank_name = this.getMeta(meta_data, '_vendor_bank_name');
    shop.aba_routing_number = this.getMeta(meta_data, '_vendor_aba_routing_number');
    shop.bank_address = this.getMeta(meta_data, '_vendor_bank_address');
    shop.destination_currency = this.getMeta(meta_data, '_vendor_destination_currency');
    shop.iban = this.getMeta(meta_data, '_vendor_iban');
    shop.account_holder_name = this.getMeta(meta_data, '_vendor_account_holder_name');

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

  getWithdrawal(id, refresher = null) {

    this.withdrawLoaded = false;

    let params: any = {
      vendor_id: id
    }

    let updateRequest = this.http.get(wordpress_url + '/wcmp-transaction.php',
      {
        params: params
      }
    ).subscribe(response => {

      console.log('withdrawal', response.json());
      if (response) {

        this.withdrawal = response.json();
        this.withdrawLoaded = true;

      }

      if (refresher) {
        refresher.complete();
      }

    }, err => {

      if (refresher) {
        refresher.complete();
      }

      console.log(err);

    });

  }

  getOrder(id, refresher = null) {

    this.orderLoaded = false;

    let params = {
      vendor: id,
      status: 'processing',
    };

    let getOrder = this.WP.get({
      wcmc: false,
      method: 'GET',
      api: 'orders',
      param: params
    });

    getOrder.subscribe(data => {

      this.orderLoaded = true;

      if (data) {

        console.log(data);

        let orders = data.json();

        orders.forEach((v, i) => {

          if (orders[i].meta_data.filter(e => e.key === 'dc_pv_shipped').length > 0) {

            let shipperArray = orders[i].meta_data.find(x => x.key === 'dc_pv_shipped').value;

            if (shipperArray.includes(id)) {
              orders[i].shipped = true;
            }
          }


        });

        this.orders = orders;

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
        this.segment = 'dashboard';
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

      this.getWithdrawal(this.user.ID);
      this.getOrder(this.user.ID);
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

  vendorOrderPage() {

    this.navCtrl.push(VendorOrdersPage,
      {
        vendor: this.customer.id,
      });

  }

  vendorWithdrawalPage() {

    this.navCtrl.push(VendorWithdrawalPage,
      {
        vendor: this.customer.id,
      });

  }

  sendVerification(user) {

    console.log(user);

    this.loader = this.loadingCtrl.create({
      content: 'Please wait...',
    });
    this.loader.present();

    let params: any = {
      id: user.ID,
    }

    let updateRequest = this.http.post(wordpress_url + '/wcmp-email-verification.php',
      params
    ).subscribe(response => {

      this.loader.dismiss();
      console.log(response);
      if (response) {

        console.log(response);
        if (response.json().status == 'success') {
          this.presentToast(response.json().message);

          const confirm = this.alertCtrl.create({
            title: 'Please re-login',
            message: 'You need to re-login after you have successfully verify your email address',
            buttons: [
              {
                text: 'Later',
                handler: () => {
                  console.log('Disagree clicked');
                }
              },
              {
                text: 'Ok',
                handler: () => {
                  console.log('Agree clicked');

                  this.storage.remove('login').then(() => {
                    this.storage.remove('user').then(() => {
                      this.storage.remove('shop');
                      this.storage.remove('customer');

                      this.loginPage();
                    })
                  });

                }
              }
            ]
          });
          confirm.present();
        } else {
          this.presentToast('Some error occured');
        }

      }

    });

  }

  resendVerification(user) {

    console.log(user);

    const confirm = this.alertCtrl.create({
      title: 'Are you sure?',
      message: 'This action will resend confirmation email to your email address',
      buttons: [
        {
          text: 'Cancel',
          handler: () => {
            console.log('Disagree clicked');
          }
        },
        {
          text: 'Yes',
          handler: () => {
            console.log('Agree clicked');
            this.sendVerification(user);

          }
        }
      ]
    });
    confirm.present();
  }

  presentToast(text) {
    let toast = this.toastCtrl.create({
      message: text,
      duration: 3000,
      position: 'top'
    });
    toast.present();
  }

  loginPage() {
    this.navCtrl.push(this.LoginPage, {});
  }

}
