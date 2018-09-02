import { Pipe, PipeTransform } from '@angular/core';

/**
 * Generated class for the FilterPipe pipe.
 *
 * See https://angular.io/api/core/Pipe for more info on Angular Pipes.
 */
@Pipe({
  name: 'filter',
})
export class FilterPipe implements PipeTransform {
  transform(items:any[], args:Object): any {
		if(args){
			for(var key in args){
				if(args.hasOwnProperty(key)){
					items = items.filter(item => item[key] == args[key]);
				}
			}
		}
		return items;
	}	
}
