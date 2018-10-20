import { NgModule, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { IonicApp, IonicErrorHandler, IonicModule } from 'ionic-angular';
import { CrossSellComponent } from './cross-sell/cross-sell';
import { WooImageComponent } from './woo-image/woo-image';
import { ButtonCartComponent } from './button-cart/button-cart';
import { PricePipe } from '../pipes/price/price';
import { GLoaderComponent } from './g-loader/g-loader';

@NgModule({
	declarations: [CrossSellComponent],
	imports: [],
	exports: [CrossSellComponent]
})
export class ComponentsModule { }

// @NgModule({
// 	declarations: [CrossSellComponent, ButtonCartComponent, WooImageComponent],
// 	imports: [
// 		IonicModule.forRoot(ButtonCartComponent)
// 	],
// 	exports: [CrossSellComponent, ButtonCartComponent, WooImageComponent],
// 	schemas: [CUSTOM_ELEMENTS_SCHEMA]
// })
// export class ComponentsModule { }
