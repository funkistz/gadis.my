import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'objectToArray',
})
export class ObjectToArrayPipe implements PipeTransform {
  transform(object:Object, args:boolean = null):Object[] {
		let array:Object[] = [];
		if(object){
			Object.keys(object).forEach((key) => {
				if(args) array.push(key);
				else array.push(object[key]);
			});
		}
		return array;
	}
}
