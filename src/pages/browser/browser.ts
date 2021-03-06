import { Component, ElementRef } from '@angular/core';
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
  callback;
  task;

  constructor(
    public hostElement: ElementRef,
    public navCtrl: NavController,
    public navParams: NavParams,
    public sanitizer: DomSanitizer,
    public storage: Storage,
  ) {

    this.username = this.navParams.get('username');

    console.log('login as : ' + this.username);

    this.callback = this.navParams.get('callback');
    let task = navParams.get('task');
    this.task = task;

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

    this.safeURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink + '?username=' + this.username + '&task=' + task);

  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad BrowserPage');
  }

  first = 0;
  onLoad() {

    console.log(this.first);

    let rule = 0;

    if (this.task == 'storefront') {
      rule = 1;
    }

    const iframe = this.hostElement.nativeElement.querySelector('iframe');
    let src = iframe.src;
    console.log(src);
    console.log(this.safeURL);

    if (this.first > rule) {

      this.callback('registered').then(() => { this.navCtrl.pop() });

    } else {
      this.first++;
    }

    // this.core.hideLoading();
    this.loaded = true;
  }

}
