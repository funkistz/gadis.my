import { Injectable } from '@angular/core';
import { Http, Headers, URLSearchParams } from '@angular/http';

@Injectable()
export class WoocommerceProvider {

  url = 'http://www.gadis.my/wc-mc.php';
  consumer_key = 'ck_d09b1a20baa9733d021692e4abf056f63599627d';
  consumer_secret = 'cs_f311e704d4787a2d51aa9389ef22aefabecd34a9';

  constructor(
    public http: Http
  ) {

  }

  checkImage(imageId) {

    if (!imageId) {
      return false;
    }

    imageId = parseInt(imageId);

    if (imageId > 0) {
      return true;
    } else {
      return false;
    }

  }

  getImageSrc(images, id, size, alt, loader) {

    if (!this.checkImage(id)) {
      return "assets/images/" + alt;
    }

    let loaderSrc = "assets/images/" + loader;

    if (images[id] && images[id] != loaderSrc) {
      return images[id];
    }

    if (!images[id]) {
      images[id] = loaderSrc;
    }

    let params = {
      id: id,
      src: true,
    };

    this.http.get('http://www.gadis.my/wp-media.php', {
      search: this.objectToURLParams(params)
    }).subscribe(res => {

      console.log(res);
      images[id] = res.text();

    });

    return images[id];

  }

  //method, wcmc, api, param
  get(option) {

    console.log(option);

    let version = 'wc/v2';
    if (option.wcmc) {
      version = 'wcmp/v1';
    }

    let headers = new Headers();
    let authorization = this.consumer_key + ':' + this.consumer_secret + ':' + version + ':' + option.api + ':' + option.method;
    headers.set('Authorization', authorization);

    if (option.method == 'GET') {

      let url = this.url;
      console.log({ url: this.url });

      if (option.param) {
        url += '?' + this.objectToUrl(option.param);
      }

      return this.http.get(url, {
        headers: headers,
      });

    } else if (option.method == 'POST') {

      if (!option.param) {
        option.param = {};
      }

      let params = this.objectToURLParams(option.param);

      return this.http.post(this.url, params, {
        headers: headers,
      })

    } else if (option.method == 'PUT') {

      if (!option.param) {
        option.param = {};
      }

      let params = this.objectToURLParams(option.param);
      console.log(params);

      return this.http.post(this.url, params, {
        headers: headers,
      })

    } else if (option.method == 'DELETE') {

      if (!option.param) {
        option.param = {};
      }

      let params = this.objectToURLParams(option.param);
      console.log(params);

      return this.http.post(this.url, params, {
        headers: headers,
      })

    }

  }

  objectToUrl(data) {
    return Object.keys(data).map(key => `${key}=${encodeURIComponent(data[key])}`).join('&');
  }

  objectToURLParams(object): URLSearchParams {
    let params: URLSearchParams = new URLSearchParams();
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        if (Array.isArray(object[key])) {
          object[key].forEach(val => {
            params.append(key + '[]', val);
          });
        }
        else params.set(key, object[key]);
      }
    }
    return params;
  }

}
