import { Component, NgZone } from '@angular/core';
import { Platform, AlertController } from 'ionic-angular';
import { StatusBar } from '@ionic-native/status-bar';
import { SplashScreen } from '@ionic-native/splash-screen';
import { Http } from '@angular/http';
import { Core } from '../service/core.service';
import { HomePage } from '../pages/home/home';
import { TabsPage } from '../pages/tabs/tabs';
import { TranslateService } from '../module/ng2-translate';
import { Storage } from '@ionic/storage';
import { Config } from '../service/config.service';
import { Network } from '@ionic-native/network';
import { ScreenOrientation } from '@ionic-native/screen-orientation';
import { Device } from '@ionic-native/device';
// import { AdMobFree, AdMobFreeBannerConfig, AdMobFreeInterstitialConfig } from '@ionic-native/admob-free';
import { GoogleAnalytics } from '@ionic-native/google-analytics';
declare var wordpress_url: string;
declare var display_mode: string;
declare var application_language: string;
declare var google_analytics: string;
declare var admob_android_banner: string;
declare var admob_android_interstitial: string;
declare var admob_ios_banner: string;
declare var admob_ios_interstitial: string;
@Component({
  templateUrl: 'app.html',
  providers: [Core, GoogleAnalytics, ScreenOrientation, Device]
})
export class MyApp {
  rootPage: any = TabsPage;
  trans: Object;
  isLoaded: boolean;
  disconnect: boolean;
  constructor(
    platform: Platform,
    private statusBar: StatusBar,
    splashScreen: SplashScreen,
    public translate: TranslateService,
    public storage: Storage,
    public http: Http,
    public core: Core,
    public config: Config,
    public ngZone: NgZone,
    public alertCtrl: AlertController,
    public Network: Network,
    public screenOrientation: ScreenOrientation,
    private device: Device,
    public ga: GoogleAnalytics
  ) {
    platform.ready().then(() => {
      statusBar.styleDefault();
      if (platform.is('android')) {
        statusBar.backgroundColorByHexString('#fff');
      }
      splashScreen.hide();
      translate.setDefaultLang(application_language);
      translate.use(application_language);
      let html = document.querySelector('html');
      html.setAttribute("dir", display_mode);
      storage.set('require', false);
      if (platform.is('cordova')) {
        screenOrientation.lock('portrait');
        let operating_system = '';
        let admob: Object = {};
        if (device.platform == 'Android') {
          operating_system = 'Android';
          admob = {
            banner: admob_android_banner,
            interstitial: admob_android_interstitial
          };
        } else if (device.platform == 'iOS') {
          operating_system = 'iOS';
          admob = {
            banner: admob_ios_banner,
            interstitial: admob_ios_interstitial
          };
        }
        if (google_analytics) {
          ga.startTrackerWithId(google_analytics).then(() => {
            ga.trackView(operating_system);
          }).catch(e => console.log('Error starting GoogleAnalytics', e));
        }
        Network.onDisconnect().subscribe(() => {
          ngZone.run(() => { this.disconnect = true; });
        });
        Network.onConnect().subscribe(() => {
          ngZone.run(() => { this.disconnect = false; });
        });
      }
    });
    storage.get('text').then(val => {
      let html = document.querySelector('html');
      html.className = val;
    });
  }
}

