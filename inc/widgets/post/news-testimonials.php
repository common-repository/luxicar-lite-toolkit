<?php

add_action( 'kpb_get_widgets_list', array('LTP_Widget_News_Testimonials', 'register_block'));
	
class LTP_Widget_News_Testimonials extends Kopa_Widget {

	public $kpb_group = 'post';
	
	public static function register_block($blocks){
       	$blocks['LTP_Widget_News_Testimonials'] = new LTP_Widget_News_Testimonials();
        return $blocks;
    }
    
	public function __construct() {
		$this->widget_cssclass    = 'widget-testimonial-1';
		$this->widget_description = esc_html__( 'Display lists article and testimonial, 2 columns.', 'luxicar-lite-toolkit' );
		$this->widget_id          = 'lucixcar-toolkit-plus-widget-news-testimonials';
		$this->widget_name        = esc_html__( 'Luxicar - News & Testimonials', 'luxicar-lite-toolkit' );
		$this->settings 		  = luxicar_toolkit_get_post_widget_args();
		$this->settings['excerpt_length'] = array(
			'type'  => 'number',
			'std'   => 20,
			'label' => esc_html__( 'Excerpt length:', 'luxicar-lite-toolkit' ),
			'desc'  => esc_html__( 'Enter 0 to hide the excerpt.', 'luxicar-lite-toolkit' ),
		);
		$cbo_tags_options = array('' => __( '-- All --', 'luxicar-lite-toolkit' ));
				
		$tags = get_terms('testimonial-tag');			
	
		if($tags && !is_wp_error($tags) ){			
			foreach ($tags as $tag) {						
				$cbo_tags_options[$tag->slug] = "{$tag->name} ({$tag->count})";
			}
		}		
		
		$this->settings['testimonial-tags'] = array(
			'type'    => 'select',
			'label'   => __( 'Tags', 'luxicar-lite-toolkit' ),
			'std'     => '',
			'options' => $cbo_tags_options
		);

		parent::__construct();
	}

	public function widget( $args, $instance ) {	
		ob_start();

		extract( $args );

		extract( $instance );
		
		echo wp_kses_post( $before_widget );

		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);		

		$query = luxicar_toolkit_get_post_widget_query($instance);
		
		$result_set = new WP_Query( $query );	

		$query_testimonial = array(
			'post_type'      => array('testimonial'),
			'posts_per_page' => (int) $instance['number'],
			'post_status'    => array('publish')
		);

		$testimonial_tags = $instance['testimonial-tags'];
		
		if(!empty($testimonial_tags)){
			$query_testimonial['tax_query'] = array(
				array(
					'taxonomy' => 'testimonial-tag',
					'field'    => 'slug',
					'terms'    => $testimonial_tags
				),
			);
		}
		
		$result_set_testimonial = new WP_Query( $query_testimonial );
			
		?>
			<?php if( $title ) : ?>
				<div class="widget-title widget-title-1">
					<h2><?php echo wp_kses_post( $before_title . $title .$after_title ); ?></h2>
				</div>
			<?php endif; ?>

			<div class="widget-content">
				<?php if($result_set->have_posts()) : ?>
					<div class="testimonial-left">
						<?php while($result_set->have_posts()) : $result_set->the_post(); ?>
							<div class="entry-item">
								<div class="time-date-1">
									<span class="month"><?php echo get_the_date( 'M' );?></span>
									<span class="day"><?php echo get_the_date( 'd' );?></span>
								</div>

								<div class="entry-content">
									<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
									<div class="meta-item"><?php esc_html_e( 'By ', 'luxicar-lite-toolkit' ); the_author_posts_link(); ?>, in <span class="location"><?php the_category(', '); ?></span></div>
									<?php
	                                	$excerpt_tmp = get_the_excerpt();
		                                if((int)$excerpt_length){ 
											$excerpt = wp_trim_words(strip_shortcodes($excerpt_tmp), $excerpt_length, '');
											echo ($excerpt) ? apply_filters( 'the_content', $excerpt ) : '';
		                                }
	                                ?>
								</div>
							</div>
						<?php endwhile; wp_reset_postdata(); ?>
					</div>
				<?php endif; ?>
				<?php if($result_set_testimonial->have_posts()) : ?>
					<div class="testimonial-right">
						<div class="widget-slider-3 load-slick">
							<?php while($result_set_testimonial->have_posts()) : $result_set_testimonial->the_post();?>
								<div class="entry-item">
									<header>
										<h3><?php the_title(); ?></h3>
										<?php 
											$star = (int) get_post_meta( get_the_id(), LTP_PREFIX . 'testimonial-star', true );
											if($star){
												echo '<div class="kopa-star">';
												for($i=1; $i <= $star; $i++){
													echo '<i class="fa fa-star"></i>';
												}
												if($star < 5){
													for($i=1; $i <= 5-$star; $i++){
														echo '<i class="fa fa-star-o"></i>';
													}
												}
												echo '</div>';
											}
										?>
									</header>

									<div class="entry-content">
										<?php
		                                	$excerpt_tmp = get_the_excerpt();
			                                if((int)$excerpt_length){ 
												$excerpt = wp_trim_words(strip_shortcodes($excerpt_tmp), $excerpt_length, '');
												echo ($excerpt) ? apply_filters( 'the_content', $excerpt ) : '';
			                                }
		                                ?>
									</div>

									<footer>
										<?php 
											$position = get_post_meta( get_the_id(), LTP_PREFIX . 'testimonial-position', true );
											$name     = get_post_meta( get_the_id(), LTP_PREFIX . 'testimonial-name', true );
											$website  = get_post_meta( get_the_id(), LTP_PREFIX . 'testimonial-website', true );
										?>
										<a href="<?php echo esc_url( $website ); ?>">
											<?php the_post_thumbnail('luxicar-widget-100x100'); ?>
										</a>
										<div class="draft">
											<h6><a href="<?php echo esc_url( $website ); ?>"><?php echo wp_kses_post( $name ); ?></a></h6>
											<p><?php echo wp_kses_post( $position ); ?></p>
										</div>
									</footer>
								</div>
							<?php endwhile; wp_reset_postdata(); ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php

		echo wp_kses_post( $after_widget );

		$content = ob_get_clean();

		echo sprintf('%s', $content);		
		
	}

}