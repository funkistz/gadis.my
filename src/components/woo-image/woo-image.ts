import { Component, Input } from '@angular/core';
import { NavController } from 'ionic-angular';
import { Config } from '../../service/config.service';

import { Storage } from '@ionic/storage';
import { CartPage } from '../../pages/cart/cart';
import { Http, Headers, URLSearchParams } from '@angular/http';

declare var wordpress_url: string;

@Component({
	selector: 'woo-image',
	templateUrl: 'woo-image.html'
})

export class WooImageComponent {

	@Input() id: string;
	@Input() size: string;
	@Input() alt: string;
	@Input() loader: string;
	@Input() type: string;
	image: string = '';

	constructor(
		public config: Config,
		public http: Http,
		public storage: Storage,
	) {

	}

	ngOnInit() {

		console.log(this.id);
		if (!this.size) {
			this.size = 'thumbnail';
		}

		if (!this.alt) {
			this.alt = 'loading-color.gif';
		}

		if (!this.loader) {
			this.loader = 'loading-wave.gif';
			this.image = 'assets/images/loading-wave.gif';
		} else {
			this.image = "assets/images/" + this.loader;
		}

		this.storage.get('woo-images').then(valueStr => {
			let value = JSON.parse(valueStr);

			if (valueStr) {

				if (value[this.id]) {

					this.image = value[this.id];

				} else {

					this.initImage();

				}

			} else {

				this.initImage();

			}


		});


	}

	initImage() {

		if (!this.loader) {
			this.loader = 'loading-wave.gif';
			this.image = 'assets/images/loading-wave.gif';
		} else {
			this.image = "assets/images/" + this.loader;
		}

		this.getImageSrc(this.size, this.alt);

	}

	getImageSrc(size, alt) {

		if (!this.checkImage(this.id)) {
			return "assets/images/" + alt;
		}

		let params = {
			id: this.id,
			src: true,
		};

		console.log(this.objectToURLParams(params));

		this.http.get(wordpress_url + '/wp-media.php', {
			search: this.objectToURLParams(params)
		}).subscribe(res => {

			console.log(res.text());
			this.image = res.text();

			this.storage.get('woo-images').then(valueStr => {

				if (valueStr) {

					let value = JSON.parse(valueStr);
					value[this.id] = res.text();
					this.storage.set('woo-images', JSON.stringify(value));

				} else {

					let array = {};
					array[this.id] = this.image;

					this.storage.set('woo-images', JSON.stringify(array));
				}


			});

		});

		return this.image;

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
