import { Component, ViewChild } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { Http } from '@angular/http';
import { Observable } from 'rxjs/Observable';

// Custom
import { Core } from '../../service/core.service';
import { Storage } from '@ionic/storage';
import { TranslateService } from '../../module/ng2-translate';
import { Toast } from '@ionic-native/toast';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';

//Pipes
import { ObjectToArrayPipe } from '../../pipes/object-to-array/object-to-array';

// Page
import { DetailPage } from '../detail/detail';
declare var wordpress_url: string;
declare var wordpress_per_page: Number;


@Component({
  selector: 'page-vendor-detail',
  templateUrl: 'vendor-detail.html',
  providers: [Core, ObjectToArrayPipe]
})
export class VendorDetailPage {

  @ViewChild('cart') buttonCart;
  DetailPage = DetailPage;
  vendor = {
    id: ''
  };
  DetailCategoryPage = VendorDetailPage;
  wpVendor: any;
  wpProducts: any;

  id: Number; page = 1; sort: string = '-date'; range: Object = { lower: 0, upper: 0 };
  data: Object = {}; favorite: Object = {}; products: Object[] = []; attributes: Object[] = [];
  filter: Object = { grid: true, open: null, value: {}, valueCustom: {} }; filtering: boolean;
  categories: Object[] = []; loaded: boolean; over: boolean;
  noResuilt: boolean = false; quantity: Number = 1; trans: Object = {};
  actionCart: Object = [];
  cartArray: Object = {};
  faded: boolean = false;
  loaddata: boolean = false;

  constructor(
    private navParams: NavParams,
    private core: Core,
    private http: Http,
    private storage: Storage,
    translate: TranslateService,
    private Toast: Toast,
    public WP: WoocommerceProvider
  ) {
    translate.get('detail').subscribe(trans => this.trans = trans);

    this.id = navParams.get('id');
    this.vendor = navParams.get('vendor');
    console.log(this.vendor);

    this.wpVendor = this.WP.get({
      wcmc: true,
      method: 'GET',
      api: 'vendors/' + this.vendor.id
    });

    this.getProducts().subscribe(products => {
      if (products && products.length > 0) {
        this.checkCart();
        this.noResuilt = false;
        this.page++;
        this.products = products;
        this.loaded = true;
        this.loaddata = true;
        setTimeout(() => {
          this.faded = true;
        }, 100);
        http.get(wordpress_url + '/wp-json/wooconnector/product/getattribute')
          .subscribe(res => {
            this.attributes = res.json();
            this.attributes['custom'] = new ObjectToArrayPipe().transform(this.attributes['custom']);
            this.reset();
          });
      } else {
        this.loaddata = true;
        this.noResuilt = true;
      }
    });
    this.loadCategories();

  }

  images = {};
  getImageSrc(images, id, size, alt, loader) {
    return this.WP.getImageSrc(images, id, size, alt, loader);
  }

  ionViewDidEnter() {
    this.checkCart();
    this.getFavorite();
    this.buttonCart.update();
  }

  checkCart() {
    this.storage.get('cart').then(val => {
      let cartNew = Object.assign([], val);
      this.cartArray = {};
      cartNew.forEach(productCart => {
        this.cartArray[productCart['id']] = productCart['id'];
      });
    });
  }

  loadCategories() {
    let params = { cat_num_page: 1, cat_per_page: 100, parent: 0 };
    this.http.get(wordpress_url + '/wp-json/wooconnector/product/getcategories', {
      search: this.core.objectToURLParams(params)
    }).subscribe(res => {
      if (res.json() && res.json().length > 0) this.categories = this.categories.concat(res.json());
      if (res.json() && res.json().length == 100) {
        params.cat_num_page++;
        this.loadCategories();
      }
    });
  };

  getFavorite() {
    this.storage.get('favorite').then(val => { if (val) this.favorite = val });
  }

  getProducts(): Observable<Object[]> {
    return new Observable(observable => {
      let tmpFilter = [];
      for (var filter in this.filter['value']) {
        let attr = this.filter['value'][filter];
        if (Object.keys(attr).length > 0) for (var option in attr) {
          if (attr[option]) {
            let now = {};
            now['keyattr'] = filter;
            now['valattr'] = option;
            now['type'] = 'attributes';
            tmpFilter.push(now);
          }
        };
      }
      for (var filter in this.filter['valueCustom']) {
        let attr = this.filter['value'][filter];
        if (attr && Object.keys(attr).length > 0) for (var option in attr) {
          if (attr[option]) {
            let now = {};
            now['keyattr'] = filter;
            now['valattr'] = option;
            now['type'] = 'custom';
            tmpFilter.push(now);
          }
        };
      }
      let params = {
        'vendor': this.vendor.id,
        'page': this.page,
        'per_page': wordpress_per_page,
      };

      params = this.addSortToSearchParams(params, this.sort);

      if (tmpFilter.length == 0 && !this.range['lower'] && !this.range['upper']) {

        //nothing happen

      } else {

        if (tmpFilter.length > 0) params['attribute'] = JSON.stringify(tmpFilter);
        if (this.range['lower'] != 0) params['min_price'] = this.range['lower'];
        if (this.range['upper'] != 0) params['max_price'] = this.range['upper'];

      }

      console.log(this.sort);

      this.wpProducts = this.WP.get({
        method: 'GET',
        api: 'products',
        param: params
      });

      this.wpProducts.subscribe(products => {

        observable.next(products.json());
        observable.complete();

      }, err => {

        console.log('error oi');
        console.log(err);

      });

    });
  }

  addSortToSearchParams(params, sort) {

    if (sort == '-date' || !sort) {
      params['order'] = 'desc';
      params['orderby'] = 'date';
    } else if (sort == 'name') {
      params['order'] = 'asc';
      params['orderby'] = 'title';
    } else if (sort == '-name') {
      params['order'] = 'desc';
      params['orderby'] = 'title';
    }

    return params;

  }

  doRefresh(refresher) {
    this.page = 1;
    this.faded = false;
    this.getProducts().subscribe(products => {
      if (products && products.length > 0) this.page++;
      this.products = [];
      this.products = products;
      this.over = false;
      setTimeout(() => {
        this.faded = true;
      }, 100);
      refresher.complete();
    });
  }

  load(infiniteScroll) {
    this.getProducts().subscribe(products => {
      if (products && products.length > 0) {
        this.page++;
        this.products = this.products.concat(products);
      } else this.over = true;
      infiniteScroll.complete();
    });
  }

  openCategory() {
    if (this.filter['open'] == 'category') this.filter['open'] = null;
    else this.filter['open'] = 'category';
  }

  openFilter() {
    if (this.filter['open'] == 'filter') this.filter['open'] = null;
    else this.filter['open'] = 'filter';
  }

  openSort() {
    if (this.filter['open'] == 'sort') this.filter['open'] = null;
    else this.filter['open'] = 'sort';
  }

  changeFavorite(product: Object) {
    if (this.favorite[product["id"]]) {
      delete this.favorite[product["id"]];
      this.storage.set('favorite', this.favorite);
    } else {
      let data: any = {
        id: product["id"],
        name: product["name"],
        regular_price: product["regular_price"],
        sale_price: product["sale_price"],
        price: product["price"],
        on_sale: product["on_sale"],
        price_html: product["price_html"],
        type: product["type"]
      };
      if (product["modernshop_images"]) data['images'] = product["modernshop_images"][0].modern_square;
      this.favorite[product["id"]] = data;
      this.storage.set('favorite', this.favorite);
    }
  }

  reset() {
    this.filter['value'] = {};
    this.filter['valueCustom'] = {};
    this.attributes['attributes'].forEach(attr => {
      this.filter['value'][attr['slug']] = {};
    });
    this.attributes['custom'].forEach(attr => {
      this.filter['valueCustom'][attr['slug']] = {};
    });
    this.range = { lower: 0, upper: 0 };
  }

  runFilter() {
    this.openFilter();
    this.page = 1;
    this.products = [];
    this.faded = false;
    this.loaded = false;
    this.loaddata = false;
    this.filtering = true;
    this.getProducts().subscribe(products => {
      if (products && products.length > 0) {
        this.page++;
        this.products = products;
        this.filtering = false;
        this.loaded = true;
        this.loaddata = true;
        setTimeout(() => {
          this.faded = true;
        }, 100);
      } else {
        this.loaddata = true;
        this.noResuilt = true;
      }
    });
  }

  runSort() {
    this.filter['open'] = null;
    this.page = 1;
    this.products = [];
    this.loaded = false;
    this.faded = false;
    this.loaddata = false;
    this.getProducts().subscribe(products => {
      if (products && products.length > 0) {
        this.page++;
        this.products = products;
        this.loaded = true;
        this.loaddata = true;
        setTimeout(() => {
          this.faded = true;
        }, 100);
      } else {
        this.loaddata = true;
        this.noResuilt = true;
      }
    });
  }

  addtoCart(detail: any) {
    if (!detail['in_stock']) {
      this.Toast.showShortBottom("Out of Stock").subscribe(
        toast => { },
        error => { console.log(error); }
      );
      return;
    }
    let data: any = {};
    let idCart = detail["id"];
    data.idCart = idCart;
    data.id = detail["id"];
    data.name = detail["name"];
    if (detail["wooconnector_crop_images"])
      data.images = detail["wooconnector_crop_images"][0].wooconnector_medium;
    data.regular_price = detail["regular_price"];
    data.sale_price = detail["sale_price"];
    data.price = detail["price"];
    data.quantity = this.quantity;
    data.sold_individually = detail['sold_individually'];
    this.storage.get('cart').then((val) => {
      let individually: boolean = false;
      if (!val) val = {};
      if (!val[idCart]) val[idCart] = data;
      else {
        if (!detail['sold_individually']) val[idCart].quantity += data.quantity;
        else individually = true;
      }
      if (individually) {
        this.Toast.showShortBottom(this.trans['individually']['before'] + detail['name'] + this.trans['individually']['after']).subscribe(
          toast => { },
          error => { console.log(error); }
        );
      } else this.storage.set('cart', val).then(() => {
        this.checkCart();
        this.buttonCart.update();
        if (!detail['in_stock'] && detail['backorders'] == 'notify') {
          this.Toast.showShortBottom(this.trans["addOut"]).subscribe(
            toast => { },
            error => { console.log(error); }
          );
        } else {
          this.Toast.showShortBottom(this.trans["add"]).subscribe(
            toast => { },
            error => { console.log(error); }
          );
        }
      });
    });
  }
}
