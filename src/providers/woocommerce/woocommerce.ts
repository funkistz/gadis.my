import { Injectable } from '@angular/core';
import { Http, Headers, URLSearchParams } from '@angular/http';

@Injectable()
export class WoocommerceProvider {

  url = 'http://www.gadis.my/wc-mc-v2.php';
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

    // console.log(option);

    let version = 'wc/v2';
    if (option.wcmc) {
      version = 'wcmp/v1';
    }
    if (!option.param) {
      option.param = {};
    }

    if (option.method != 'GET') {
      option.params = this.objectToURLParams(option.param);
    }

    let headers = new Headers();
    let authorization = this.consumer_key + ':' + this.consumer_secret + ':' + version + ':' + option.api + ':' + option.method;
    headers.set('Authorization', authorization);
    // headers.set('authorization', authorization);
    // headers.set('x-api-key', authorization);
    // headers.set('Cache-control', 'no-cache');
    // headers.set('Cache-control', 'no-store');
    // headers.set('Expires', '0');
    // headers.set('Pragma', 'no-cache');

    if (option.method == 'GET') {

      let url = this.url;

      if (option.param) {
        option.param.timestamp = + new Date();
        url += '?' + this.objectToUrl(option.param);
      }

      return this.http.get(url, {
        headers: headers,
      });

    } else if (option.method == 'POST') {

      return this.http.post(this.url, option.param, {
        headers: headers,
      })

    } else if (option.method == 'PUT') {

      return this.http.post(this.url, option.param, {
        headers: headers,
      })

    } else if (option.method == 'DELETE') {

      return this.http.post(this.url, option.param, {
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
