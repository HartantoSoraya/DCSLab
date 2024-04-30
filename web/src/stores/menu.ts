import { type Icon } from "@/components/Base/Lucide/Lucide.vue";
import { type Themes } from "@/stores/theme";
import { defineStore } from "pinia";
import sideMenu from "@/main/side-menu";
import simpleMenu from "@/main/simple-menu";
import topMenu from "@/main/top-menu";

export interface Menu {
  icon: Icon;
  title: string;
  pageName?: string;
  subMenu?: Menu[];
  ignore?: boolean;
}

export interface MenuState {
  menuValue: Array<Menu | "divider">;
}

export const useMenuStore = defineStore("menu", {
  state: (): MenuState => ({
    menuValue: [
      {
        icon: 'Home',
        pageName: 'side-menu-dashboard',
        title: 'Dashboard',
        subMenu: [
          {
            icon: "ChevronRight",
            pageName: "side-menu-dashboard-maindashboard",
            title: "Main Dashboard",
          }
        ]
      }
    ],
  }),
  getters: {
    menu: (state) => (layout: Themes["layout"]) => {
      if (layout == "top-menu") {
        return topMenu;
      }

      if (layout == "simple-menu") {
        return simpleMenu;
      }

      return sideMenu;
    },
  },
  actions: {
    setUserMenu(userMenu: Array<Menu>) {
      this.menuValue = userMenu;
    }
  }
});
