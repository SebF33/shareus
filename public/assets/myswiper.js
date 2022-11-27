const swiper = new Swiper('.swiper-container', {
  slidesPerView: 1,
  loop: true,
  autoplay: {
    delay: 4000,
    disableOnInteraction: false
  },
  allowTouchMove: true,
  pagination: {
    el: '.swiper-pagination',
    clickable: true
  },
});