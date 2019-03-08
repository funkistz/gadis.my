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
import moment from 'moment';

declare var cordova: any;

@Component({
  selector: 'page-create-product',
  templateUrl: 'create-product.html',
  providers: [StorageMulti, Device, Core]
})
export class CreateProductPage {

  WooCommerce: any;
  formEdit: FormGroup;
  categories = [];
  conditions = [];
  colours = [];
  categoryName = null;
  attributes: any;

  vendor: any;
  loader: any;
  loading = true;
  images: any = [
    // 'assets/imgs/logo.png',
    // 'assets/imgs/logo.png',
    // 'assets/imgs/logo.png'
  ];
  public today = moment();
  public date: string = new Date().toISOString();

  // for update
  product: any = {
    name: '',
    regular_price: '',
    price: '',
    short_description: '',
    manage_stock: false,
    stock_quantity: '',
    backorders: 'no',
    in_stock: true,
    sold_individually: false
  };

  callback;

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

    this.vendor = this.navParams.get('vendor');

    console.log('vendor is ' + this.vendor);

    if (this.navParams.get('product')) {
      this.product = { ...this.navParams.get('product') };
    }

    this.formEdit = formBuilder.group({
      brand: ['', Validators.compose([Validators.maxLength(50)])],
      name: ['', Validators.compose([Validators.maxLength(50), Validators.required])],
      regular_price: ['', Validators.compose([Validators.required])],
      sale_price: ['', Validators.compose([Validators.required, Validators.min(0)])],
      short_description: ['', Validators.compose([Validators.maxLength(255)])],
      category: ['', Validators.compose([Validators.required])],
      pa_condition: ['', Validators.compose([Validators.required])],
      pa_colour: [''],
      manage_stock: [''],
      stock_quantity: [''],
      backorders: [''],
      in_stock: [''],
      sold_individually: [''],
    }, { validator: this.matchValidator });

    if (this.product) {

      let app = this;

      setTimeout(function () { app.setProduct(); }, 200);

      console.log(this.product);
    }

    this.storageMul.get(['last_sync_attribute', 'attribute']).then(val => {

      if (val['last_sync_attribute'] && val['attribute']) {

        if (this.today.isAfter(new Date(val['last_sync_attribute']), "hour")) {

          console.log('not today attribute');
          this.getAttribute();
        } else {

          this.attributes = val['attribute'];
          this.loading = false;
        }

      } else {

        this.getAttribute();

      }

    });

  }

  matchValidator(group: FormGroup) {
    // console.log('checking...');
    var valid = false;

    if (group.controls['regular_price'].value >= group.controls['sale_price'].value) {
      // console.log(group.controls['regular_price'].value + ' >= ' + group.controls['sale_price'].value);
      valid = true;
    }

    if (valid) {
      return null;
    }

    return {
      mismatch: true
    };
  }

  validatorRegularPrice(control) {

    if (control.value >= this.product.price) {
      return true;
    } else {
      return false;
    }

  }

  validatorSalePrice(control) {

    if (control.value <= this.product.regular_price) {
      return true;
    } else {
      return false;
    }

  }

  setProduct() {

    console.log('set product');
    console.log(this.product);

    let product = this.product;
    let app = this;

    if (product.images) {

      if (product.images.length > 0) {

        product.images.forEach(function (value) {
          app.images.push(value.src);
        });

      }

    }

    if (product.short_description) {

      let description = product.short_description.replace(/<(?:.|\n)*?>/gm, '');

      this.formEdit.controls['short_description'].setValue(description);

    }

    if (product.meta_data) {

      if (product.meta_data.length > 0) {

        let brand = product.meta_data.find(e => e.key === 'wpcf-brands');
        if (brand) {
          this.formEdit.controls['brand'].setValue(brand.value);
        }

      }

    }

    if (product.categories) {

      if (product.categories.length > 0) {
        this.categoryName = product.categories[0].name;
        this.formEdit.controls['category'].setValue(product.categories[0].id);
      }

    }

    if (product.attributes) {

      if (product.attributes.length > 0) {

        console.log('attr exist');

        let colour = product.attributes.find(e => e.name === 'Colour');
        if (colour) {
          this.formEdit.controls['pa_colour'].setValue(colour.options[0]);
          console.log('colour');
          console.log(colour.options[0]);
        }

        let condition = product.attributes.find(e => e.name === 'Condition');
        if (condition) {
          this.formEdit.controls['pa_condition'].setValue(condition.options[0]);
        }

      }

    }

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

  conditionRequest: any;
  colourRequest: any;
  getAttribute() {

    console.log('get attribute...');

    this.http.get('http://www.gadis.my/wp-json/wooconnector/product/getattribute')
      .subscribe(res => {
        console.log(res.json());
        this.attributes = res.json();
        this.storage.set('last_sync_attribute', this.date);
        this.storage.set('attribute', res.json());
        this.loading = false;
      });

  }

  uploadedImages = [];
  async uploadImages(image) {

    var url = "http://www.gadis.my/wp-upload.php";
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
        this.uploadedImages.push(uploaded.url);
        resolve();

      }, err => {

        console.log(err);
        reject('upload image failed');

      });

    });

  }

  async addProduct() {

    this.loader = this.loadingCtrl.create({
      content: 'Uploading images...',
    });
    this.loader.present();

    let stop = false;
    this.uploadedImages = [];

    await Promise.all(this.images.map(async (file) => {

      if (!file.startsWith('http')) {

        if (!stop) {

          //enable this after finished debug
          const contents = await this.uploadImages(file).catch(error => stop = true);
          console.log(contents);
        }

      } else {

        // this.uploadedImages.push(file);

      }

    }));

    this.loader.dismiss();

    if (stop) {

      console.log('Upload image failed.');
      this.presentToast('Upload image failed');
      return;
    }

    this.presentToast('Upload image completed');
    this.loader = this.loadingCtrl.create({
      content: 'Please wait...',
    });
    this.loader.present();

    let temp = this.formEdit.value;

    let vendorID = this.vendor;

    //get categories
    let category = [
      { 'id': temp.category }
    ];

    let images = [];
    let attributes = [];

    if (temp.pa_colour) {
      attributes.push(
        {
          "id": 1,
          "name": "Colour",
          "visible": 1,
          "options": [
            temp.pa_colour
          ]
        }
      );
    }

    if (temp.pa_condition) {
      attributes.push(
        {
          "id": 2,
          "name": "Condition",
          "visible": 1,
          "options": [
            temp.pa_condition
          ]
        }
      );
    }

    let meta_data = [
      {
        "key": "wpcf-brands",
        "value": temp.brand
      }
    ];

    this.uploadedImages.forEach(([key, src]) => {
      images.push({
        src: src,
        position: key
      });
    });

    let params: any = {
      vendor: vendorID,
      meta_data: JSON.stringify(meta_data),
      name: temp.name,
      regular_price: temp.regular_price,
      sale_price: temp.sale_price,
      short_description: temp.short_description,
      categories: JSON.stringify(category),
      attributes: JSON.stringify(attributes),
      manage_stock: temp.manage_stock,
      in_stock: temp.in_stock,
      sold_individually: temp.sold_individually
    }

    if (temp.stock_quantity) {
      params.stock_quantity = temp.stock_quantity;
    }

    if (temp.backorders) {
      params.backorders = temp.backorders;
    }

    if (images.length > 0) {

      params.images = JSON.stringify(images);

    }

    if (this.product.id) {

      if (this.product.images) {

        if (this.product.images.length > 0) {

          this.product.images.forEach(function (value) {
            console.log(value);

            images.push(value);

          });

          params.images = JSON.stringify(images);

        }

      }

      this.WooCommerce = this.WP.get({
        wcmc: false,
        method: 'PUT',
        api: 'products/' + this.product.id,
        param: params
      });

    } else {

      this.WooCommerce = this.WP.get({
        wcmc: false,
        method: 'POST',
        api: 'products',
        param: params
      });

    }

    console.log('parameter');
    console.log(params);

    // this.loader.dismiss();

    try {

      this.WooCommerce.subscribe(response => {

        if (response) {

          console.log(response);
          if (response.json().id) {

            this.loader.dismiss();

            if (this.product) {
              this.presentToast('Product Succefully Updated');

            } else {
              this.presentToast('Product Succefully Added');

            }

          }

        }

        this.callback('updated').then(() => { this.navCtrl.pop() });
      });

    }
    catch (e) {

      this.loader.dismiss();
      this.presentToast('Some error occured');

    }


  }

  getPictures() {
    console.log('enter get picture');

    let maxImages = 3 - this.images.length;

    let options = {
      maximumImagesCount: maxImages
    };

    this.imagePicker.getPictures(options).then((imagePath) => {
      for (var i = 0; i < imagePath.length; i++) {

        // imagePath[i] = normalizeURL(imagePath[i]);

        if (this.device.platform === 'Android') {

          this.filePath.resolveNativePath(imagePath[i])
            .then(filePath => {

              console.log(filePath);

              let correctPath = filePath.substr(0, filePath.lastIndexOf('/') + 1);
              let currentName = filePath.substr(filePath.lastIndexOf('/') + 1);

              console.log('from library');
              console.log(currentName);
              console.log(correctPath);

              this.copyFileToLocalDir(correctPath, currentName, this.createFileName());
            });
        } else {

          // imagePath[i] = normalizeURL(imagePath[i]);
          console.log(imagePath[i]);
          var correctPath = imagePath[i].substr(0, imagePath[i].lastIndexOf('/') + 1);
          var currentName = imagePath[i].substr(imagePath[i].lastIndexOf('/') + 1);

          console.log('from camera');
          console.log(currentName);
          console.log(correctPath);

          this.copyFileToLocalDir(correctPath, currentName, this.createFileName());
        }

        // imagePath[i] = normalizeURL(imagePath[i]);
        // console.log(imagePath[i]);
        // // this.images.push(imagePath[i]);

        // var currentName = imagePath[i].substr(imagePath[i].lastIndexOf('/') + 1);
        // var correctPath = imagePath[i].substr(0, imagePath[i].lastIndexOf('/') + 1);

        // correctPath = normalizeURL(correctPath);

        // console.log(currentName);
        // console.log(correctPath);

        // this.copyFileToLocalDir(correctPath, currentName, this.createFileName());

      }
    }, (err) => { });

  }

  takePicture(sourceType) {

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
      // Special handling for Android library
      if (this.device.platform === 'Android' && sourceType == this.camera.PictureSourceType.PHOTOLIBRARY) {
        this.filePath.resolveNativePath(imagePath)
          .then(filePath => {
            let correctPath = filePath.substr(0, filePath.lastIndexOf('/') + 1);
            let currentName = imagePath.substring(imagePath.lastIndexOf('/') + 1, imagePath.lastIndexOf('?'));

            console.log('from library');
            console.log(currentName);
            console.log(correctPath);

            this.copyFileToLocalDir(correctPath, currentName, this.createFileName());
          });
      } else {

        console.log(imagePath);
        var currentName = imagePath.substr(imagePath.lastIndexOf('/') + 1);
        var correctPath = imagePath.substr(0, imagePath.lastIndexOf('/') + 1);

        console.log('from camera');
        console.log(currentName);
        console.log(correctPath);

        this.copyFileToLocalDir(correctPath, currentName, this.createFileName());
      }
    }, (err) => {
      this.images.push('assets/imgs/logo.png');
      this.presentToast('Error while selecting image.');
    });
  }

  prevImage(id) {
    let element = this.images[id];
    let nextID = id - 1;

    if (nextID < 0) {
      nextID = this.images.length - 1;
    }

    this.images.splice(id, 1);
    this.images.splice(nextID, 0, element);
  }

  nextImage(id) {
    let element = this.images[id];
    let nextID = id + 1;

    if (nextID >= this.images.length) {
      nextID = 0;
    }

    this.images.splice(id, 1);
    this.images.splice(nextID, 0, element);
  }

  deletePhoto(index, image) {

    this.images.splice(index, 1);

    if (this.product.id) {
      this.product.images = this.product.images.filter(function (obj) {
        return obj.src !== image;
      });
    }

  }

  presentActionSheet() {
    let actionSheet = this.actionSheetCtrl.create({
      title: 'Select Image Source',
      buttons: [
        {
          text: 'Load from album (multiple)',
          handler: () => {
            // this.takePicture(this.camera.PictureSourceType.PHOTOLIBRARY);
            this.getPictures();
          }
        },
        {
          text: 'Load from album (crop)',
          handler: () => {
            this.takePicture(this.camera.PictureSourceType.PHOTOLIBRARY);
            // this.getPictures();
          }
        },
        {
          text: 'Use Camera',
          handler: () => {
            this.takePicture(this.camera.PictureSourceType.CAMERA);
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

  copyFileToLocalDir(namePath, currentName, newFileName) {

    this.file.copyFile(namePath, currentName, cordova.file.dataDirectory, newFileName).then(success => {

      console.log(success);
      this.images.push(newFileName);

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

    if (img.startsWith('http')) {
      return img;
    }

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
