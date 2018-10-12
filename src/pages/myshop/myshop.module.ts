import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { MyshopPage } from './myshop';

@NgModule({
  declarations: [
    MyshopPage,
  ],
  imports: [
    IonicPageModule.forChild(MyshopPage),
  ],
})
export class MyshopPageModule {}
