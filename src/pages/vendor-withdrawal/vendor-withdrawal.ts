import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, AlertController, LoadingController } from 'ionic-angular';
import { StorageMulti } from '../../service/storage-multi.service';
import { Core } from '../../service/core.service';
import { Device } from '@ionic-native/device';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';
import { Storage } from '@ionic/storage';
import { Toast } from '@ionic-native/toast';
import { Http, Headers } from '@angular/http';

import { DetailOrderVendorPage } from '../detail-order-vendor/detail-order-vendor';

declare var wordpress_url: string;
declare var date_format: string;

@Component({
  selector: 'page-vendor-withdrawal',
  templateUrl: 'vendor-withdrawal.html',
  providers: [StorageMulti, Device, Core]
})
export class VendorWithdrawalPage {

  DetailOrderPage = DetailOrderVendorPage;
  loader: any;

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
    public loadingCtrl: LoadingController,
    public core: Core,
    public Toast: Toast,
    public http: Http

  ) {

    this.vendor = navParams.get('vendor');

  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad Myshop2Page');
  }

  ionViewDidEnter() {
    this.getWithdrawal();
  }

  getWithdrawal(refresher = null) {

    this.orderLoaded = false;

    let params: any = {
      vendor_id: this.vendor,
      status: this.status,
      withdrawal: true
    }

    let updateRequest = this.http.get(wordpress_url + '/wcmp-transaction.php',
      {
        params: params
      }
    ).subscribe(response => {

      console.log('withdrawal', response);
      if (response) {

        this.orders = response.json();
        this.orderLoaded = true;

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

  doRefresh(refresher) {

    refresher.complete();
    this.getWithdrawal();

  }

  refreshPage() {

    this.getWithdrawal();

  }

  requestWithdrawal(order_id, commission_id) {

    const confirm = this.alertCtrl.create({
      title: 'Are sure want to request withdrawal for the order #' + order_id + '?',
      message: 'This withdrawal will be transfer to your bank account that you have set previously after the approval from admin.',
      buttons: [
        {
          text: 'Cancel',
          handler: () => {
            console.log('Cancel clicked');
          }
        },
        {
          text: 'Request',
          handler: () => {
            this.withdrawal(commission_id);
          }
        }
      ]
    });
    confirm.present();

  }

  withdrawal(commission_id) {

    this.loader = this.loadingCtrl.create({
      content: 'Please wait...',
    });
    this.loader.present();

    let params: any = {
      vendor_id: this.vendor,
      commission_id: [commission_id]
    }

    let updateRequest = this.http.post(wordpress_url + '/wcmp-transaction.php',
      params
    ).subscribe(response => {

      this.loader.dismiss();
      if (response) {

        let datax = response['_body'];

        datax = datax.substring(datax.indexOf("{") + 1);
        datax = '{' + datax;
        datax = JSON.parse(datax);

        console.log(datax);
        if (datax.status == 'success') {
          this.presentToast(datax.message);

          this.getWithdrawal();
        } else {
          this.presentToast('Some error occured');
        }

      }

    });

  }

  presentToast(text) {
    let toast = this.toastCtrl.create({
      message: text,
      duration: 3000,
      position: 'top'
    });
    toast.present();
  }

}
