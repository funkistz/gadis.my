import { Component, ElementRef } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { DomSanitizer } from '@angular/platform-browser';
import { Storage } from '@ionic/storage';

@Component({
  selector: 'page-vendor-register',
  templateUrl: 'vendor-register.html',
})
export class VendorRegisterPage {


  externalLink = 'https://www.gadis.my/redirectVendor.php';
  loaded = false;
  safeURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink);
  title: String = '';

  username;
  callback;

  constructor(
    public hostElement: ElementRef,
    public navCtrl: NavController,
    public navParams: NavParams,
    public sanitizer: DomSanitizer,
    public storage: Storage,
  ) {

    this.callback = this.navParams.get('callback');
    let task = 'dashboard';

    this.storage.get('login').then((login) => {

      console.log(login);
      this.safeURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink + '?username=' + login.user_display_name + '&task=' + task);
      this.username = login.username;

    });

  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad BrowserPage');
  }

  first = 0;
  onLoad() {

    const iframe = this.hostElement.nativeElement.querySelector('iframe');
    let src = iframe.src;
    console.log(src);
    console.log(this.safeURL);

    if (this.first > 0) {

      this.callback('registered').then(() => { this.navCtrl.pop() });

    } else {
      this.first++;
    }

    // this.core.hideLoading();
    this.loaded = true;
  }

}
