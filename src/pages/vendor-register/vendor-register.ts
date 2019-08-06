import { Component, ElementRef } from '@angular/core';
import { IonicPage, NavController, NavParams, Platform } from 'ionic-angular';
import { DomSanitizer } from '@angular/platform-browser';
import { Storage } from '@ionic/storage';
import { Device } from '@ionic-native/device';

@Component({
  selector: 'page-vendor-register',
  templateUrl: 'vendor-register.html',
  providers: [Device]
})
export class VendorRegisterPage {


  externalLink = 'https://www.gadis.my/redirectVendor.php';
  loaded = false;
  safeURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink);
  title: String = '';

  username;
  callback;
  type;

  constructor(
    public hostElement: ElementRef,
    public navCtrl: NavController,
    public navParams: NavParams,
    public sanitizer: DomSanitizer,
    public storage: Storage,
    public platform: Platform,
    private device: Device,
  ) {

    this.username = this.navParams.get('username');
    this.callback = this.navParams.get('callback');
    this.type = this.navParams.get('type');

    let task = this.type;

    this.safeURL = this.sanitizer.bypassSecurityTrustResourceUrl(this.externalLink + '?username=' + this.username + '&task=' + task);

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

      if (this.device.platform === 'Android') {

        if (this.first > 1) {
          this.callback('registered').then(() => { this.navCtrl.pop() });
        }

      } else {

        this.callback('registered').then(() => { this.navCtrl.pop() });

      }


    } else {
      this.first++;
    }

    // this.core.hideLoading();
    this.loaded = true;
  }

}
