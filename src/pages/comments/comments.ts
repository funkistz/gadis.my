import { Component } from '@angular/core';
import { NavController, NavParams, ModalController } from 'ionic-angular';
import { Http } from '@angular/http';
import { Observable } from 'rxjs/Observable';
// Custom
import { Core } from '../../service/core.service';

//Page
import { RatingPage } from '../rating/rating';
declare var wordpress_url: string;

@Component({
  selector: 'page-comments',
  templateUrl: 'comments.html',
  providers: [Core]
})
export class CommentsPage {
	id: Number;
	comments: any;
	allow: boolean;
	lastComment: Object[];
	page: any = 1;
	over: boolean;
	loaddata: boolean;
	faded: boolean = false;
	constructor(
		public navCtrl: NavController,
		public navParams: NavParams,
		private http: Http,
		private core: Core,
		public modalCtrl: ModalController
	) {
		this.id = navParams.get("id");
		this.allow = navParams.get("allow");
		this.getDataComment();
	}
	getDataComment() {
		this.comments = { total: 0, details: { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 } };
		this.getReview(true).subscribe(res => {
			this.comments["reviews"] = res;
			this.lastComment = this.comments["reviews"];
			if (!this.comments["reviews"]) this.comments["reviews"] = [];
			this.calculator();
			this.loaddata = true;
			setTimeout(() => {
				this.faded = true;
			},100);
		});
	}
	getReview(reload: boolean = true): Observable<Object[]> {
		return new Observable(observable => {
			this.http.get(wordpress_url + '/wp-json/wooconnector/product/getnewcomment', {
				search: this.core.objectToURLParams({
					'product_id': this.id,
					'post_per_page': 10,
					'post_num_page': this.page
				})
			}).subscribe(res => {
				observable.next(res.json());
				observable.complete();
			});
		});
	}
	loadMore(infiniteScroll) {
		this.page++;
		this.getReview(true).subscribe(res => {
			if (res && res.length > 0) {
				this.comments["reviews"] = this.comments.concat(res);
				this.calculator();
			} else this.over = true;
			infiniteScroll.complete();
		});
	}
	doRefresh(refresher) {
		this.page = 1;
		this.faded = false;
		this.comments["reviews"] = [];
		this.getReview(true).subscribe(res => {
			if (res && res.length > 0) this.page++;
			this.comments["reviews"] = res;
			this.over = false;
			setTimeout(() => {
				this.faded = true;
			},500);
			refresher.complete();
		});
	}
	calculator() {
		this.comments["reviews"].forEach((val) => {
			this.comments["total"] += Number(val.rating);
			this.comments["details"][val.rating] += 1;
		});
		this.bestRating();
		this.setPercent();
	}
	bestRating() {
		if (this.comments["reviews"].length == 0) return this.comments["best"] = 0;
		this.comments["best"] = Object.keys(this.comments["details"]).reduce((a, b) => {
			return this.comments["details"][a] > this.comments["details"][b] ? a : b;
		});
	}
	setPercent() {
		if (this.comments["best"] != null) {
			let best = this.comments["details"][this.comments["best"]];
			this.comments["percent"] = [];
			for (let i = 5; i >= 1; i--) {
				this.comments["percent"].push({
					rating: i,
					percent: ((this.comments["details"][i] / best * 100) || 0) + '%'
				});
			}
		}
	}
	showRating() {
		if (this.allow) {
			let modal = this.modalCtrl.create(RatingPage, { id: this.id });
			modal.onDidDismiss(reload => {
				console.log(reload);
				this.page = 1;
				this.getReview(reload);
			});
			modal.present();
		}
	}
}
