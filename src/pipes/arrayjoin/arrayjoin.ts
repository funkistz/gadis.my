import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'arrayjoin',
})
export class ArrayjoinPipe implements PipeTransform {
  transform(value:any[], args:string = ', ') {
		if(value) return value.join(args);
	}
}
