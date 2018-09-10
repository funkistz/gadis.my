import { Component, ViewChild } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';

import { Http } from '@angular/http';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';

// Custom
import { Core } from '../../service/core.service';

declare var wordpress_url: string;


@IonicPage()
@Component({
  selector: 'page-vendors',
  templateUrl: 'vendors.html',
})
export class VendorsPage {

  @ViewChild('cart') buttonCart;

  parents: Object[] = [];
  id: Number;
  noResuilt: boolean = false;
  faded: boolean = false;
  loaddata: boolean = false;
  WooCommerce: any;

  constructor(
    public http: Http,
    public core: Core,
    public navCtrl: NavController,
    public WP: WoocommerceProvider
  ) {

    this.WooCommerce = WP.init();

    this.WooCommerce.getAsync("products").then((data) => {
      console.log(JSON.parse(data.body));
    }, (err) => {
      console.log(err)
    })


    // let params = { cat_num_page: 1, cat_per_page: 100, parent: '0' };
    // let loadCategories = () => {
    //   http.get(wordpress_url + '/wp-json/wcmp/v1/vendors', {
    //     search: core.objectToURLParams(params)
    //   }).subscribe(res => {

    //     console.log(res);

    //     // this.loaddata = true;
    //     // this.parents = this.parents.concat(res.json());
    //     // setTimeout(() => {
    //     //   this.faded = true;
    //     // }, 100);
    //     // if (res.json() && res.json().length == 100) {
    //     //   this.noResuilt = false;
    //     //   params.cat_num_page++;
    //     //   loadCategories();
    //     // } else {
    //     //   this.loaddata = true;
    //     //   this.noResuilt = true;
    //     // }
    //   });
    // };
    // loadCategories();
  }
  ionViewDidEnter() {
    this.buttonCart.update();
  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad VendorsPage');
  }

}
