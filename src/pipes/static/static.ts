import { Pipe, PipeTransform } from '@angular/core';
import { Config } from '../../service/config.service';

@Pipe({
  name: 'static',
})
export class StaticPipe implements PipeTransform {
  textStatic:Object = {};
	
	constructor(public config: Config){
		if(config['text_static']) this.textStatic = config['text_static'];
	}
	transform(value) {
		if(this.textStatic[value]) return this.textStatic[value];
		else return null;
	}
}
