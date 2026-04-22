<?php
/**
 * KWL Coupon WP — Index (Fallback Template)
 *
 * WordPress uses this as a fallback when no more specific
 * template exists. For a coupon site this mostly handles
 * the blog post archive if a blog is enabled.
 *
 * @package KWL_Coupon_WP
 */

get_header();
?>

<main id="kwl-main" class="cwp-main" role="main">
    <div class="cwp-container">
        <div class="cwp-layout <?php echo kwl_get_option( 'layout' ) === 'full-width' ? 'cwp-layout--full' : ''; ?>">

            <div class="cwp-main-content">

                <!-- Page Title -->
                <?php if ( is_home() && ! is_front_page() ) : ?>
                <header class="cwp-archive-header">
                    <h1 class="cwp-archive-title"><?php esc_html_e( 'Latest Posts', 'kwl-coupon-wp' ); ?></h1>
                </header>
                <?php endif; ?>

                <!-- Posts Loop -->
                <?php if ( have_posts() ) : ?>

                    <div class="cwp-posts-list">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class( 'cwp-post-card' ); ?>>

                                <?php if ( has_post_thumbnail() ) : ?>
                                <a href="<?php the_permalink(); ?>" class="cwp-post-card__thumb">
                                    <?php the_post_thumbnail( 'medium_large', [ 'loading' => 'lazy' ] ); ?>
                                </a>
                                <?php endif; ?>

                                <div class="cwp-post-card__body">
                                    <header>
                                        <h2 class="cwp-post-card__title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h2>
                                        <div class="cwp-post-card__meta">
                                            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                                <?php echo esc_html( get_the_date() ); ?>
                                            </time>
                                        </div>
                                    </header>
                                    <div class="cwp-post-card__excerpt">
                                        <?php the_excerpt(); ?>
                                    </div>
                                    <a href="<?php the_permalink(); ?>" class="cwp-btn cwp-btn--outline cwp-btn--sm">
                                        <?php esc_html_e( 'Read More', 'kwl-coupon-wp' ); ?>
                                    </a>
                                </div>

                            </article>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <?php kwl_render_pagination(); ?>

                <?php else : ?>

                    <div class="cwp-empty-state">
                        <p><?php esc_html_e( 'No posts found.', 'kwl-coupon-wp' ); ?></p>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cwp-btn cwp-btn--primary">
                            <?php esc_html_e( 'Back to Homepage', 'kwl-coupon-wp' ); ?>
                        </a>
                    </div>

                <?php endif; ?>

            </div><!-- /.cwp-main-content -->

            <?php get_sidebar(); ?>

        </div><!-- /.cwp-layout -->
    </div><!-- /.cwp-container -->
</main>

<?php get_footer(); ?>
