import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { DomSanitizer } from '@angular/platform-browser';
import { Storage } from '@ionic/storage';

@Component({
  selector: 'page-browser',
  templateUrl: 'browser.html'
})
export class BrowserPage {

  externalLink = 'https://www.gadis.my/redirectVendor.php';
  loaded = false;
  safeURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink);
  title: String = '';

  username;

  constructor(
    public navCtrl: NavController,
    public navParams: NavParams,
    public sanitizer: DomSanitizer,
    public storage: Storage,
  ) {

    let task = navParams.get('task');

    if (task == 'storefront') {
      this.title = 'Store Front';
    } else if (task == 'vendor-orders') {
      this.title = 'Orders';
    } else if (task == 'add-product') {
      this.title = 'Add Product';
    } else if (task == 'products') {
      this.title = 'All Products';
    } else if (task == 'vendor-withdrawal') {
      this.title = 'Payments Withdrawal';
    } else if (task == 'transaction-details') {
      this.title = 'Payments History';
    } else if (task == 'vendor-report') {
      this.title = 'Reports';
    } else if (task == 'dashboard') {
      this.title = 'Dashboard';
    }

    if (task.indexOf('add-product/') !== -1) {
      this.title = 'Edit Product';
    }

    this.storage.get('login').then((login) => {

      console.log(login);
      this.safeURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink + '?username=' + login.user_display_name + '&task=' + task);
      this.username = login.username;

    });

  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad BrowserPage');
  }

  onLoad() {
    // this.core.hideLoading();
    this.loaded = true;
  }

}
