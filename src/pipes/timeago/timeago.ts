import { Pipe, PipeTransform } from '@angular/core';
import { TranslateService } from '../../module/ng2-translate';
import { DatePipe } from '@angular/common';

declare var date_format:string;
@Pipe({
  name: 'timeago'
})
export class TimeagoPipe implements PipeTransform {
  trans:Object;
	
	constructor(translate:TranslateService, private datePipe: DatePipe){
		translate.get('general.timeAgo').subscribe(trans => this.trans = trans);
	}
	transform(value) {
		let _value:any;
		let ago = (new Date().getTime() - new Date(value).getTime())/1000;
		if(ago < 0) _value = this.datePipe.transform(value, date_format);
		else if(ago < 3600){
			_value = Math.floor(ago/60);
			if(_value < 2) _value += this.trans['minute'];
			else _value += this.trans['minutes'];
		}
		else if(ago < 86400){
			_value = Math.floor(ago/3600);
			if(_value < 2) _value += this.trans['hour'];
			else _value += this.trans['hours'];
		}
		else if(ago < 2592000){
			_value = Math.floor(ago/86400);
			if(_value < 2) _value += this.trans['day'];
			else _value += this.trans['days'];
		}
		else _value = this.datePipe.transform(value, date_format);
		return _value;
	}
}
