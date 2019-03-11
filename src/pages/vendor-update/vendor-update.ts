import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, ModalController, ActionSheetController, Platform, normalizeURL, LoadingController } from 'ionic-angular';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Device } from '@ionic-native/device';

import { Storage } from '@ionic/storage';
import { StorageMulti } from '../../service/storage-multi.service';
import { InAppBrowser } from '@ionic-native/in-app-browser';
import { Core } from '../../service/core.service';
import { WoocommerceProvider } from '../../providers/woocommerce/woocommerce';
import { Http } from '@angular/http';
import { ImagePicker } from '@ionic-native/image-picker';
import { Camera, CameraOptions } from '@ionic-native/camera';
import { File } from '@ionic-native/file';
import { FileTransfer, FileUploadOptions, FileTransferObject } from '@ionic-native/file-transfer';
import { FilePath } from '@ionic-native/file-path';

import { ModalCategoryPage } from '../../pages/modal-category/modal-category';

declare var cordova: any;
declare var wordpress_url: string;

@Component({
  selector: 'page-vendor-update',
  templateUrl: 'vendor-update.html',
  providers: [StorageMulti, Device, Core]
})
export class VendorUpdatePage {

  WooCommerce: any;
  formEdit: FormGroup;
  categories = [];
  conditions = [];
  colours = [];
  categoryName = null;

  shop: any;
  loader: any;
  loading = true;

  images: any = [];
  avatar: any;
  banner: any;

  callback;

  states = [
    'Selangor',
    'Kuala Lumpur',
    'Johor',
    'Kedah',
    'Kelantan',
    'Melaka',
    'Negeri sembilan',
    'Pahang',
    'Penang',
    'Perak',
    'Perlis',
    'Sabah',
    'Sarawak',
    'Terengganu'
  ];

  constructor(
    public navCtrl: NavController,
    public navParams: NavParams,
    private device: Device,
    public storage: Storage,
    public storageMul: StorageMulti,
    public toastCtrl: ToastController,
    public loadingCtrl: LoadingController,
    public WP: WoocommerceProvider,
    public formBuilder: FormBuilder,
    public core: Core,
    public http: Http,
    private theInAppBrowser: InAppBrowser,
    public modalCtrl: ModalController,
    public actionSheetCtrl: ActionSheetController,
    public platform: Platform,
    public imagePicker: ImagePicker,
    public camera: Camera,
    private file: File,
    private filePath: FilePath,
    private transfer: FileTransfer
  ) {

    this.callback = this.navParams.get('callback');

    this.storageMul.get(['shop', 'customer']).then(val => {

      console.log(val["customer"].id);
      this.shop = val["shop"];
      this.shop['id'] = val["customer"].id;
      this.shop['country'] = 'Malaysia';

      console.log(val["shop"]);

    });

    // this.shop = this.navParams.get('shop');

    this.formEdit = formBuilder.group({
      name: ['', Validators.compose([Validators.maxLength(50), Validators.required])],
      description: ['', Validators.compose([Validators.maxLength(255), Validators.required])],
      message_to_buyers: ['', Validators.compose([Validators.maxLength(255)])],
      phone: ['', Validators.compose([Validators.maxLength(13)])],
      // email: ['', Validators.compose([Validators.maxLength(100), Validators.required])],
      address_1: ['', Validators.compose([Validators.maxLength(25)])],
      address_2: ['', Validators.compose([Validators.maxLength(25)])],
      city: ['', Validators.compose([Validators.maxLength(25)])],
      state: ['', Validators.compose([Validators.maxLength(25)])],
      country: ['', Validators.compose([Validators.maxLength(25)])],
      postcode: ['', Validators.compose([Validators.maxLength(8)])]
    });

  }

  modalCategory() {
    let categoryModal = this.modalCtrl.create(ModalCategoryPage);
    categoryModal.onDidDismiss(data => {

      if (data) {
        console.log(data);

        this.categoryName = data.name;
        this.formEdit.controls['category'].setValue(data.id);
      }

    });
    categoryModal.present();
  }

  uploadedAvatar: any;
  uploadedBanner: any;
  async uploadImages(image, type) {

    var url = wordpress_url + "/wp-upload.php";
    var targetPath = this.pathForImage(image);
    var filename = image;
    console.log(targetPath);
    console.log(filename);

    var options = {
      fileKey: "file",
      fileName: filename,
      chunkedMode: false,
      mimeType: "multipart/form-data",
      params: { 'fileName': filename }
    };

    const fileTransfer: FileTransferObject = this.transfer.create();

    return new Promise((resolve, reject) => {

      fileTransfer.upload(targetPath, url, options).then(data => {

        console.log(data.response);
        let uploaded = JSON.parse(data.response);

        if (type == 'avatar') {
          this.uploadedAvatar = uploaded;
        } else {
          this.uploadedBanner = uploaded;
        }
        resolve();

      }, err => {

        console.log(err);
        reject('upload image failed');

      });

    });

  }

  async update() {

    if (this.images['avatar'] || this.images['banner']) {

      this.loader = this.loadingCtrl.create({
        content: 'Uploading images...',
      });
      this.loader.present();

      let stop = false;

      console.log('image length : ' + this.images.length);

      if (this.images['avatar']) {
        const contents = await this.uploadImages(this.images['avatar'], 'avatar').catch(error => stop = true);
        console.log(contents);
      }

      if (this.images['banner']) {
        const contents = await this.uploadImages(this.images['banner'], 'banner').catch(error => stop = true);
        console.log(contents);
      }

      this.loader.dismiss();

      if (stop) {

        console.log('Upload image failed.');
        this.presentToast('Upload image failed');
        return;
      }

      this.presentToast('Upload image completed');
      console.log('avatar');
      console.log(this.uploadedAvatar);

      console.log('banner');
      console.log(this.uploadedBanner);

    }

    this.loader = this.loadingCtrl.create({
      content: 'Please wait...',
    });
    this.loader.present();

    let temp = this.formEdit.value;

    console.log('update vendor : ' + this.shop.id);

    let params: any = {
      id: this.shop.id,
      title: temp.name,
      description: temp.description,
      message_to_buyers: temp.message_to_buyers,
      address_1: temp.address_1,
      address_2: temp.address_2,
      city: temp.city,
      state: temp.state,

      //set to always malaysia
      country: 'MY',
      postcode: temp.postcode,
      phone: temp.phone,
    }

    if (this.uploadedAvatar) {
      params.image = this.uploadedAvatar.id;
    }

    if (this.uploadedBanner) {
      params.banner = this.uploadedBanner.id;
    }

    console.log('updating');
    let updateRequest = this.http.post(wordpress_url + '/wcmp-vendor.php',
      params
    ).subscribe(response => {

      this.loader.dismiss();
      console.log(response);
      if (response) {

        console.log(response);
        if (response.json().status == 'success') {

          this.presentToast('Shop Succefully Updated');
          this.callback('updated').then(() => { this.navCtrl.pop() });
        } else {
          this.presentToast('Some error occured');
        }

      }

    });

  }

  getPictures() {
    console.log('enter get picture');
    let options = {
      maximumImagesCount: 3
    };

    this.imagePicker.getPictures(options).then((imagePath) => {
      for (var i = 0; i < imagePath.length; i++) {

        imagePath[i] = normalizeURL(imagePath[i]);
        console.log(imagePath[i]);
        // this.images.push(imagePath[i]);

        var currentName = imagePath[i].substr(imagePath[i].lastIndexOf('/') + 1);
        var correctPath = imagePath[i].substr(0, imagePath[i].lastIndexOf('/') + 1);

        correctPath = normalizeURL(correctPath);

        console.log(currentName);
        console.log(correctPath);

        // this.copyFileToLocalDir(correctPath, currentName, this.createFileName());

      }
    }, (err) => { });

  }

  takePicture(sourceType, type) {

    console.log('enter take picture');
    console.log(this.device.platform);

    // Create options for the Camera Dialog
    var options = {
      quality: 50,
      sourceType: sourceType,
      saveToPhotoAlbum: false,
      correctOrientation: true,
      allowEdit: true
    };

    // Get the data of an image
    this.camera.getPicture(options).then((imagePath) => {

      console.log('before process');
      console.log(imagePath);

      // Special handling for Android library
      if (this.device.platform === 'Android' && sourceType == this.camera.PictureSourceType.PHOTOLIBRARY) {
        this.filePath.resolveNativePath(imagePath)
          .then(filePath => {
            let correctPath = filePath.substr(0, filePath.lastIndexOf('/') + 1);
            let currentName = imagePath.substring(imagePath.lastIndexOf('/') + 1, imagePath.lastIndexOf('?'));

            console.log('from library');
            console.log(currentName);
            console.log(correctPath);

            this.copyFileToLocalDir(correctPath, currentName, this.createFileName(), type);
          });
      } else {

        console.log(imagePath);
        var currentName = imagePath.substr(imagePath.lastIndexOf('/') + 1);
        var correctPath = imagePath.substr(0, imagePath.lastIndexOf('/') + 1);

        console.log('from camera');
        console.log(currentName);
        console.log(correctPath);

        this.copyFileToLocalDir(correctPath, currentName, this.createFileName(), type);
      }
    }, (err) => {

      if (type == 'banner') {
        this.banner = 'assets/images/person.png';
        this.images['banner'] = 'assets/images/person.png';
      } else {
        this.avatar = 'assets/images/person.png';
        this.images['avatar'] = 'assets/images/person.png';
      }

      this.presentToast('Error while selecting image.');
    });
  }

  presentActionSheet(type) {
    let actionSheet = this.actionSheetCtrl.create({
      title: 'Select Image Source',
      buttons: [
        // {
        //   text: 'Load from Library',
        //   handler: () => {
        //     // this.takePicture(this.camera.PictureSourceType.PHOTOLIBRARY);
        //     this.getPictures();
        //   }
        // },
        {
          text: 'Load from Album',
          handler: () => {
            this.takePicture(this.camera.PictureSourceType.PHOTOLIBRARY, type);
            // this.getPictures();
          }
        },
        {
          text: 'Use Camera',
          handler: () => {
            this.takePicture(this.camera.PictureSourceType.CAMERA, type);
          }
        },
        {
          text: 'Cancel',
          role: 'cancel'
        }
      ]
    });
    actionSheet.present();
  }

  createFileName() {
    var d = new Date(),
      n = d.getTime(),
      newFileName = n + ".jpg";
    return newFileName;
  }

  copyFileToLocalDir(namePath, currentName, newFileName, type) {

    this.file.copyFile(namePath, currentName, cordova.file.dataDirectory, newFileName).then(success => {

      console.log(cordova.file.dataDirectory);
      console.log(newFileName);
      console.log(success.fullPath);
      console.log(success.nativeURL);
      if (type == 'banner') {
        this.images['banner'] = success.nativeURL;
      } else {
        this.images['avatar'] = success.nativeURL;
      }

    }, error => {
      console.log(error);
      this.presentToast('Failed to load image.');
    });
  }

  presentToast(text) {
    let toast = this.toastCtrl.create({
      message: text,
      duration: 3000,
      position: 'top'
    });
    toast.present();
  }

  pathForImage(img) {

    if (img.includes('file:///')) {
      return img;
    }

    try {

      if (img === null) {
        return '';
      } else {
        return cordova.file.dataDirectory + img;
      }

    } catch (error) {

      return img;

    }


  }

}
