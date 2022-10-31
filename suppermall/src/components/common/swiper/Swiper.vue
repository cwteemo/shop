<template>
  <div id="hy-swiper">
    <div class="swiper" @touchstart="touchStart" @touchmove="touchMove" @touchend="touchEnd">
      <slot></slot>
    </div>
    <slot name="indicator"></slot>
    <div class="indicator">
      <slot name="indicator" v-if="showIndicators && slideCount>1">
        <div v-for="(item,index) in slideCount" class="indi-item" :class="{active:index === currentIndex-1}" :key="index">

        </div>
      </slot>
    </div>
  </div>
</template>

<script>
export default {
  name: "Swiper",
  pops: {
    //多长时间轮播一次
    interval: {
      type: Number,
      default: 3000
    },
    //延迟多久开始轮播
    animDuration: {
      type: Number,
      default: 300000
    },
    //拖动比例
    moveRatio: {
      type: Number,
      default: 0.25
    },
    //指示器（显示的第几个轮播图）
    showIndicator: {
      type: Boolean,
      default: true
    },
  },
  data: function () {
    return {
      slideCount: 0, //元素个数
      totalWidth: 0, //swiper宽度
      swiperStyle: {}, //swiper样式
      currentIndex: 1, //当前的index
      scrolling: false, //是否正在滚动
      showIndicators: this.showIndicator, //是否正在滚动
    }
  },
  mounted: function () {
    //1.操作DOM，在前后添加slide
    setTimeout(() => {
      this.handelDom();
      //开启定时器
      this.startTimer();
    }, 1000)
  },

  methods: {
    //定时器操作
    startTimer: function () {
      this.playTimer = window.setInterval(() => {
        this.currentIndex++;
        this.scrollContent(-this.currentIndex * this.totalWidth)
      }, this.interval)
    },
    stopTimer: function () {
      window.clearInterval(this.playTimer)
    },

    /**
     * 滚动到正确位置
     * @param currentPosition
     */
    scrollContent: function (currentPosition) {
      //0.设置正在滚动
      this.scrolling = true;
      //1.开始滚动动画
      this.swiperStyle.transition = 'transform' + this.animDuration + 'ms';
      this.setTransform(currentPosition);
      //2. 判断当前位置
      this.checkPosition();
      //滚动完成
      this.scrolling = false;
    },

    /**
     *  校验位置
     */
    checkPosition: function () {
      window.setTimeout(() => {
        //1.校验正确位置
        this.swiperStyle.transition = '0ms';
        if (this.currentIndex >= this.slideCount + 1) {
          this.currentIndex = 1;
          this.setTransform(-this.currentIndex * this.totalWidth);
        } else if (this.currentIndex <= 0) {
          this.currentIndex = this.slideCount;
          this.setTransform(-this.currentIndex * this.totalWidth);
        }

        // 2.结束移动后回调
        this.$emit('transitionEnd', this.currentIndex - 1);
      }, this.animDuration)
    },

    /**
     * 设置滚动位置
     */
    setTransform: function (position) {
      this.swiperStyle.transform = `translate3d(${position}px, 0, 0)`;
      this.swiperStyle['-webkit-transform'] = `translate3d(${position}px), 0, 0`;
      this.swiperStyle['-ms-transform'] = `translate3d(${position}px), 0, 0`;
    },
    /**
     * 操作DOM，在DOM前后添加slide
     */
    handelDom: function (){
      //1.获取要操作的元素
      let swiperEl = document.querySelector('.swiper');
      let slidesEls = swiperEl.getElementsByClassName('slide');

      //2.保存个数
      this.slideCount = slidesEls.length;
      //3.如果大于1个，那么在前后分别加一个slide
      if (this.slideCount > 1){
        let cloneFirst = slidesEls[0].cloneNode(true);
        let cloneLast = slidesEls[this.slideCount - 1].cloneNode(true);
        swiperEl.insertBefore(cloneLast,slidesEls[0]);
        swiperEl.appendChild(cloneFirst);
        this.totalWidth = swiperEl.offsetWidth;
        this.swiperStyle = swiperEl.style;

      }
      //4. 让swiper元素，显示第一个（目前是显示前面添加最后一个元素）
      this.setTransform(-this.totalWidth);
    },

    /**
     * 拖动事件处理
     */
    touchStart: function (e){
      //1.如果正在滚动，不可以拖动
      if (this.scrolling) return;
      //2.停止定时器
      this.stopTimer();
      //保存开始滚动位置
      this.startX = e.touches[0].pageX;
    },

    touchMove: function (e){
      //计算出用户拖动的距离
      this.currentX = e.touches[0].pageX;
      this.distance = this.currentX - this.startX;
      let currentPosition = -this.currentIndex * this.totalWidth;
      let moveDistance = this.distance + currentPosition;
      //设置当前位置
      this.setTransform(moveDistance)

    },

    touchEnd: function (e){
      //获取移动距离
      let currentMove = Math.abs(this.distance);

      //判断最终距离
      if (this.distance === 0){
        return
      }else if (this.distance > 0 && currentMove > this.totalWidth * this.moveRatio){
        //右边移动超过0.5
        this.currentIndex--;
      }else if (this.distance > 0 && currentMove > this.totalWidth * this.moveRatio){
        //左边移动超过0.5
        this.currentIndex++;
      }

      //移动到正确位置
      this.scrollContent(-this.currentIndex * this.totalWidth);

      //移动完成重启定时器
      this.startTimer()
    },
    /**
     * 控制上一个下一个
     */

    previous: function (){
      this.changeItem(-1);
    },

    next: function (){
      this.changeItem(1);
    },

    changeItem: function (num){
      //移除定时器
      this.stopTimer();
      //修改index和位置
      this.currentIndex += num;
      this.scrollContent(-this.currentIndex * this.totalWidth);

      //添加定时器
      this.startTimer();
    }

  }
}
</script>

<style scoped>
  #hy-swiper {
    overflow: hidden;
    position: relative;
  }

  .swiper {
    display: flex;
  }
  .indicator {
    display: flex;
    justify-content: center;
    position: absolute;
    width: 100%;
    bottom: 8px;
  }

  .indi-item {
    box-sizing: border-box;
    width: 8px;
    height: 8px;
    border-radius: 4px;
    background-color: #fff;
    line-height: 8px;
    text-align: center;
    font-size: 12px;
    margin: 0 5px;
  }

  .indi-item.active {
    background-color: rgba(212,62,46,1.0);
  }
</style>