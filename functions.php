
/*reviews*/
function vm_google_reviews_shortcode( $atts ) {

    $atts = shortcode_atts(
        array(
            'data_id' => ''
        ),
        $atts,
        'google_reviews'
    );

    if ( empty( $atts['data_id'] ) ) {
        return '<p>Google Reviews data_id is required.</p>';
    }

    if ( ! defined( 'SERPAPI_KEY' ) || empty( SERPAPI_KEY ) ) {
        return '<p>SerpAPI key is missing.</p>';
    }

    $cache_key = 'vm_google_reviews_' . md5( $atts['data_id'] );

    $data = get_transient( $cache_key );

    if ( false === $data ) {

        $url = add_query_arg(
            array(
                'engine'  => 'google_maps_reviews',
                'data_id' => $atts['data_id'],
                'hl'      => 'en',
                'api_key' => SERPAPI_KEY,
            ),
            'https://serpapi.com/search.json'
        );

        $response = wp_remote_get(
            $url,
            array(
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            return '<p>Unable to fetch reviews.</p>';
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( empty( $data['reviews'] ) ) {
            return '<p>No reviews found.</p>';
        }

        set_transient( $cache_key, $data, 12 * HOUR_IN_SECONDS );
    }

    ob_start();
    ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <style>
    .g-review-card{
        background:rgba(0,0,0,.55);
        backdrop-filter:blur(6px);
        border-radius:14px;
        padding:18px 20px;
        color:#f4f4f4;
        box-shadow:0 6px 18px rgba(0,0,0,.25);
    }

    .g-review-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
    }

    .g-review-left{
        display:flex;
        align-items:center;
        gap:10px;
    }

    .g-avatar,
    .g-avatar-image{
        width:48px;
        height:48px;
        border-radius:50%;
        flex-shrink:0;
    }

    .g-avatar{
        background:rgba(255,255,255,.15);
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        font-size:18px;
        font-weight:600;
    }

    .g-avatar-image{
        object-fit:cover;
    }

    .g-name{
        font-size:14px;
        font-weight:600;
        color:#fff;
    }

    .g-time{
        font-size:10px;
        color:#ddd;
    }

    .g-stars{
        margin:10px 0;
    }

    .g-stars span{
        font-size:14px;
        color:#ffcc66;
    }

    .g-review-text{
        font-size:14px;
        color:#eee;
        line-height:1.45;
    }

    .gReviewsSwiper{
        overflow:hidden;
        padding-bottom:40px;
    }

    .gReviewsSwiper .swiper-slide{
        height:auto;
    }

    .swiper-button-next,
    .swiper-button-prev{
        display:none !important;
    }
    </style>

    <div class="swiper gReviewsSwiper">
        <div class="swiper-wrapper">

            <?php foreach ( $data['reviews'] as $review ) :

                $user_name = $review['user']['name'] ?? '';
                $avatar    = $review['user']['thumbnail'] ?? '';
                $rating    = $review['rating'] ?? 5;
                $date      = $review['date'] ?? '';
                $snippet   = $review['snippet'] ?? '';
            ?>

                <div class="swiper-slide">

                    <div class="g-review-card">

                        <div class="g-review-header">

                            <div class="g-review-left">

                                <?php if ( $avatar ) : ?>

                                    <img
                                        src="<?php echo esc_url( $avatar ); ?>"
                                        alt="<?php echo esc_attr( $user_name ); ?>"
                                        class="g-avatar-image"
                                    >

                                <?php else : ?>

                                    <div class="g-avatar">
                                        <?php echo esc_html( strtoupper( substr( $user_name, 0, 1 ) ) ); ?>
                                    </div>

                                <?php endif; ?>

                                <div>

                                    <div class="g-name">
                                        <?php echo esc_html( $user_name ); ?>
                                    </div>

                                    <div class="g-time">
                                        <?php echo esc_html( $date ); ?>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="g-stars">
                            <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                <span><?php echo ( $i <= $rating ) ? '★' : '☆'; ?></span>
                            <?php endfor; ?>
                        </div>

                        <div class="g-review-text">
                            <?php echo esc_html( wp_trim_words( $snippet, 40, '...' ) ); ?>
                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="swiper-pagination"></div>
    </div>

 

    <?php

    return ob_get_clean();
}

add_shortcode( 'google_reviews', 'vm_google_reviews_shortcode' );
