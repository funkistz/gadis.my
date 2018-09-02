import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'viewmore',
})
export class ViewmorePipe implements PipeTransform {
  transform(text, limit) {
		if(!limit) limit = 150;
		if(text.length <= limit) return text;
		text = text ? String(text).replace(/<[^>]+>/gm, '') : '';
		text = text.replace(/(\r\n|\n|\r)/gm,"");
		text = text.split(" ");
		let newText = [];
		for(var i = 0; i < 15; i++){
			if(text[i]) newText.push(text[i]);
		}
		text = newText.join(" ");
		if(text.length > limit) text = text.substring(0, limit);
		return text+'...';
	}
}
