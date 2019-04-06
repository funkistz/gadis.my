import { Component, ViewChild } from '@angular/core';
import { NavController } from 'ionic-angular';
import { CameraOptions, Camera } from '@ionic-native/camera';

@Component({
  selector: 'page-image-editor',
  templateUrl: 'image-editor.html',
})
export class ImageEditorPage {

  cropperOptions: any;
  croppedImage = null;

  myImage = null;
  scaleValX = 1;
  scaleValY = 1;

  constructor(public navCtrl: NavController, private camera: Camera) {
    this.cropperOptions = {
      dragMode: 'crop',
      aspectRatio: 1,
      autoCrop: true,
      movable: true,
      zoomable: true,
      scalable: true,
      autoCropArea: 0.8,
    };
  }


}
