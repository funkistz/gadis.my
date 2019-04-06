import { Component } from '@angular/core';

import { HomePage } from '../home/home';
import { CategoriesPage } from '../categories/categories';
import { SearchPage } from '../search/search';
import { AccountPage } from '../account/account';
import { VendorsPage } from '../vendors/vendors';
import { MyshopPage } from '../myshop/myshop';
import { Myshop2Page } from '../myshop2/myshop2';

@Component({
  templateUrl: 'tabs.html'
})
export class TabsPage {

  HomePage = HomePage;
  CategoriesPage = CategoriesPage;
  SearchPage = SearchPage;
  AccountPage = AccountPage;
  VendorsPage = VendorsPage;
  MyshopPage = Myshop2Page;

  // HomePage = MyshopPage;

  // tab1Root = VendorsPage;
  // tab5Root = HomePage;


  constructor() {

  }
}
