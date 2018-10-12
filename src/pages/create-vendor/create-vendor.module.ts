import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { CreateVendorPage } from './create-vendor';

@NgModule({
  declarations: [
    CreateVendorPage,
  ],
  imports: [
    IonicPageModule.forChild(CreateVendorPage),
  ],
})
export class CreateVendorPageModule {}
