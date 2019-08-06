import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, AlertController } from 'ionic-angular';
import { StorageMulti } from '../../service/storage-multi.service';
import { Core } from '../../service/core.service';
import { Device } from '@ionic-native/device';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';
import { Storage } from '@ionic/storage';
import { Toast } from '@ionic-native/toast';

import { DetailOrderVendorPage } from '../detail-order-vendor/detail-order-vendor';

@Component({
  selector: 'page-vendor-orders',
  templateUrl: 'vendor-orders.html',
  providers: [StorageMulti, Device, Core]
})
export class VendorOrdersPage {

  DetailOrderPage = DetailOrderVendorPage;

  vendor: number;
  orders = [];
  orderLoaded = false;
  status = 'all';

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

    this.vendor = navParams.get('vendor');

  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad Myshop2Page');
  }

  ionViewDidEnter() {
    this.getOrder();
  }

  getOrder(refresher = null) {

    this.orderLoaded = false;

    console.log('enter get order');

    let params = {};

    if (this.status == "all") {
      params = {
        vendor: this.vendor,
      };
    } else {

      params = {
        vendor: this.vendor,
        status: this.status
      };

    }


    let getOrder = this.WP.get({
      wcmc: false,
      method: 'GET',
      api: 'orders',
      param: params
    });

    getOrder.subscribe(data => {

      this.orderLoaded = true;

      console.log(data);

      if (data) {

        let orders = data.json();

        orders.forEach((v, i) => {

          if (orders[i].meta_data.filter(e => e.key === 'dc_pv_shipped').length > 0) {

            let shipperArray = orders[i].meta_data.find(x => x.key === 'dc_pv_shipped').value;

            if (shipperArray.includes(this.vendor)) {
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

  doRefresh(refresher) {

    refresher.complete();
    this.getOrder();

  }

  refreshPage() {

    this.getOrder();

  }

}
