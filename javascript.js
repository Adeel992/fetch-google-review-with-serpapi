   <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function(){

        document.querySelectorAll('.gReviewsSwiper').forEach(function(el){

            new Swiper(el,{
                slidesPerView:4,
                spaceBetween:30,
                loop:true,
                speed:8000,
                freeMode:true,
                freeModeMomentum:false,
                autoplay:{
                    delay:0,
                    disableOnInteraction:false
                },
                breakpoints:{
                    320:{slidesPerView:1},
                    768:{slidesPerView:2},
                    1024:{slidesPerView:4}
                }
            });

        });

    });
    </script>
