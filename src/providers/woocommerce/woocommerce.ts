import { Injectable } from '@angular/core';
import * as WC from 'woocommerce-api';


@Injectable()
export class WoocommerceProvider {

  Woocommerce: any;
  WoocommerceV2: any;

  constructor() {
    this.Woocommerce = WC({
      url: "https://www.gadis.my",
      consumerKey: "ck_d09b1a20baa9733d021692e4abf056f63599627d",
      consumerSecret: "cs_f311e704d4787a2d51aa9389ef22aefabecd34a9"
    });

    this.WoocommerceV2 = WC({
      url: "https://www.gadis.my",
      consumerKey: "ck_d09b1a20baa9733d021692e4abf056f63599627d",
      consumerSecret: "cs_f311e704d4787a2d51aa9389ef22aefabecd34a9",
      wpAPI: true,
      version: "wc/v2"
    });
  }

  init(v2?: boolean) {
    return this.WoocommerceV2;
  }

}
