import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, AlertController } from 'ionic-angular';
import { StorageMulti } from '../../service/storage-multi.service';
import { Core } from '../../service/core.service';
import { Device } from '@ionic-native/device';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';
import { Storage } from '@ionic/storage';
import { Toast } from '@ionic-native/toast';
import { Observable } from 'rxjs/Observable';

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
  page = 1;
  over: boolean;
  noOrder: boolean = false;

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

    this.page = 1;
    this.getData().subscribe(order => {
      if (order.length > 0) {
        this.noOrder = false;
        this.page++;
        this.orders = order;
      } else {
        this.noOrder = true;
      }
    });
  }

  // getOrder(refresher = null) {

  //   this.orderLoaded = false;

  //   console.log('enter get order');

  //   let params = {};

  //   if (this.status == "all") {
  //     params = {
  //       vendor: this.vendor,
  //     };
  //   } else {

  //     params = {
  //       vendor: this.vendor,
  //       status: this.status
  //     };

  //   }

  //   let getOrder = this.WP.get({
  //     wcmc: false,
  //     method: 'GET',
  //     api: 'orders',
  //     param: params
  //   });

  //   getOrder.subscribe(data => {

  //     this.orderLoaded = true;

  //     console.log(data);

  //     if (data) {

  //       let orders = data.json();

  //       orders.forEach((v, i) => {

  //         if (orders[i].meta_data.filter(e => e.key === 'dc_pv_shipped').length > 0) {

  //           let shipperArray = orders[i].meta_data.find(x => x.key === 'dc_pv_shipped').value;

  //           if (shipperArray.includes(this.vendor)) {
  //             orders[i].shipped = true;
  //           }
  //         }


  //       });

  //       this.orders = orders;

  //     }

  //     if (refresher) {
  //       refresher.complete();
  //     }

  //   }, err => {

  //     if (refresher) {
  //       refresher.complete();
  //     }

  //     console.log('error oi');

  //   });

  // }

  getData(hide: boolean = false): Observable<Object[]> {

    this.orderLoaded = false;

    return new Observable(observable => {
      if (!hide) this.core.showLoading();

      let params = {};

      if (this.status == "all") {
        params = {
          vendor: this.vendor,
          page: this.page,
          per_page: 10
        };
      } else {

        params = {
          vendor: this.vendor,
          status: this.status,
          page: this.page,
          per_page: 10
        };

      }

      let getOrder = this.WP.get({
        wcmc: false,
        method: 'GET',
        api: 'orders',
        param: params
      });

      getOrder.subscribe(data => {
        let orders = data.json();

        orders.forEach((v, i) => {

          if (orders[i].meta_data.filter(e => e.key === 'dc_pv_shipped').length > 0) {

            let shipperArray = orders[i].meta_data.find(x => x.key === 'dc_pv_shipped').value;

            if (shipperArray.includes(this.vendor)) {
              orders[i].shipped = true;
            }
          }
        });

        console.log('orders', orders);

        if (!hide) this.core.hideLoading();
        this.orderLoaded = true;

        observable.next(orders);
        observable.complete();

      }, err => {
        if (!hide) this.core.hideLoading();
        this.orderLoaded = true;

        this.Toast.showShortBottom(err.json()["message"]).subscribe(
          toast => { },
          error => { console.log(error); }
        );
      });

    });
  }

  load(infiniteScroll) {
    this.getData(true).subscribe(order => {
      if (order.length > 0) this.page++;
      else this.over = true;
      this.orders = this.orders.concat(order);
      infiniteScroll.complete();
    });
  }

  doRefresh(refresher) {

    this.page = 1;
    this.getData(true).subscribe(order => {
      this.over = false;
      if (order.length > 0) this.page++;
      this.orders = order;
      refresher.complete();
    });

  }

}
