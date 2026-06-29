
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
