import { Component } from '@angular/core';

import { HomePage } from '../home/home';
import { CategoriesPage } from '../categories/categories';
import { SearchPage } from '../search/search';
import { AccountPage } from '../account/account';
import { VendorsPage } from '../vendors/vendors';

@Component({
  templateUrl: 'tabs.html'
})
export class TabsPage {

  tab1Root = HomePage;
  tab2Root = CategoriesPage;
  tab3Root = SearchPage;
  tab4Root = AccountPage;
  tab5Root = VendorsPage;

  // tab1Root = VendorsPage;


  constructor() {

  }
}
