<template>
  <tab-bar>
    <tab-bar-item path="/home" active-color="deeppink">
      <img slot="item-icon" src="~assets/img/tab-bar/home.jpg" alt="">
      <img slot="item-icon-active" src="~assets/img/tab-bar/home-active.jpg" alt="">
      <div slot="item-text">首页</div>
    </tab-bar-item>
    <tab-bar-item path="/category">
      <img slot="item-icon" src="~assets/img/tab-bar/category.jpg" alt="">
      <img slot="item-icon-active" src="~assets/img/tab-bar/category-active.jpg" alt="">
      <div slot="item-text">分类</div>
    </tab-bar-item>
    <tab-bar-item path="/cart">
      <img slot="item-icon" src="~assets/img/tab-bar/car.jpg" alt="">
      <img slot="item-icon-active" src="~assets/img/tab-bar/car-active.jpg" alt="">
      <div slot="item-text">购物车</div>
    </tab-bar-item>
    <tab-bar-item path="/profile">
      <img slot="item-icon" src="~assets/img/tab-bar/profile.jpg" alt="">
      <img slot="item-icon-active" src="~assets/img/tab-bar/profile-active.jpg" alt="">
      <div slot="item-text">我的</div>
    </tab-bar-item>
  </tab-bar>
</template>

<script>
import TabBar from "components/common/tabbar/TabBar";
import TabBarItem from "components/common/tabbar/TabBarItem";

export default {
  name: "MainTabBar",
  components: {
    TabBar,
    TabBarItem,
  }
}
</script>

<style scoped>

</style>
