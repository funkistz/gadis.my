import { Component, ViewChild } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';

import { Http } from '@angular/http';
import { Core } from '../../service/core.service';

import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';
import { VendorDetailPage } from '../vendor-detail/vendor-detail';

@IonicPage()
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
  vendors: any = [];
  VendorDetailPage = VendorDetailPage;

  constructor(
    public http: Http,
    public navCtrl: NavController,
    public WP: WoocommerceProvider
  ) {


  }

  images = {};
  getImageSrc(images, id, size, alt, loader) {
    return this.WP.getImageSrc(images, id, size, alt, loader);
  }

  ionViewDidEnter() {
    this.buttonCart.update();

    this.WooCommerce = this.WP.get({
      wcmc: true,
      method: 'GET',
      api: 'vendors'
    });

    this.WooCommerce.subscribe(data => {

      this.loaddata = true;
      this.vendors = data.json();
      console.log(data.json());


    }, err => {

      console.log('error oi');

    });
  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad VendorsPage');

  }

}
