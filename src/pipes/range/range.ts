import { Pipe, PipeTransform } from '@angular/core';

/**
 * Generated class for the RangePipe pipe.
 *
 * See https://angular.io/api/core/Pipe for more info on Angular Pipes.
 */
@Pipe({
  name: 'range',
})
export class RangePipe implements PipeTransform {
  transform(items:any[], args:Number[]): any {
		if(args && args.length == 2){
			//items = items.filter(item => item[key] == args[key]);
		}
		return items;
	}	
}
