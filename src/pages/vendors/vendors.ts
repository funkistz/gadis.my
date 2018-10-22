import { Component, ViewChild } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';

import { Http } from '@angular/http';
import { Core } from '../../service/core.service';

import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';
import { VendorDetailPage } from '../vendor-detail/vendor-detail';

@Component({
  selector: 'page-vendors',
  templateUrl: 'vendors.html',
  providers: [Core]
})
export class VendorsPage {

  @ViewChild('cart') buttonCart;

  parents: Object[] = [];
  WooCommerce: any;
  loaddata: boolean = false;
  tempVendors: any = [];
  vendors: any = [];
  VendorDetailPage = VendorDetailPage;

  constructor(
    public http: Http,
    public navCtrl: NavController,
    public WP: WoocommerceProvider
  ) {

    this.getData();

  }

  images = {};
  getImageSrc(images, id, size, alt, loader) {
    return this.WP.getImageSrc(images, id, size, alt, loader);
  }

  ionViewDidEnter() {
    this.buttonCart.update();
  }

  search(ev: any) {

    if (!this.loaddata) {
      return;
    }

    this.vendors = this.tempVendors;

    const val = ev.target.value;

    // if the value is an empty string don't filter the items
    if (val && val.trim() != '') {
      this.vendors = this.vendors.filter((item) => {
        return (item.shop.title.toLowerCase().indexOf(val.toLowerCase()) > -1);
      })
    }

  }

  getData() {

    this.loaddata = false;
    this.vendors = [];

    this.WooCommerce = this.WP.get({
      wcmc: true,
      method: 'GET',
      api: 'vendors',
      param: {
        'per_page': 100
      }
    });

    this.WooCommerce.subscribe(data => {

      this.loaddata = true;
      this.tempVendors = data.json();
      this.vendors = data.json();
      console.log(data.json());


    }, err => {

      console.log('error oi');

    });

  }

  doRefresh(refresher) {
    refresher.complete();
    this.getData();
  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad VendorsPage');

  }

}
