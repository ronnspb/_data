<div class="ccb-tab-container thank-you-page-main" :class="[`ccb-thank-you-${$store.getters.getId}`]" style="padding: <?php echo esc_attr( defined( 'CCB_PRO_VERSION' ) ? '0' : '16px 17px' ); ?>">
	<?php if ( ! defined( 'CCB_PRO' ) ) : ?>
		<div class="ccb-grid-box">
			<div class="container">
				<div class="row">
					<div class="ccb-is-pro">
						<div class="ccb-is-pro__label">
							<span><?php esc_html_e( 'Confirmation page', 'cost-calculator-builder' ); ?></span>
						</div>
						<div class="ccb-is-pro__container">
							<div class="ccb-is-pro__header">
								<div class="ccb-is-pro__title-box">
									<span class="ccb-is-pro__icon-box">
										<img src="<?php echo esc_attr( esc_url( CALC_URL . '/frontend/dist/img/lock.png' ) ); ?>" alt="lock svg" width="24" height="24">
									</span>
									<span class="ccb-is-pro__text">
										<?php esc_html_e( 'Confirmation Page is available in Pro version', 'cost-calculator-builder' ); ?>
									</span>
								</div>
								<div class="ccb-is-pro__action">
									<a href="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=calcwpadmin&utm_medium=freetoprobutton&utm_campaign=confirmationpage" target="_blank" class="ccb-button ccb-href success"><?php esc_html_e( 'Upgrade Now', 'cost-calculator-builder' ); ?></a>
								</div>
							</div>
							<div class="ccb-is-pro__content">
								<div class="ccb-is-pro__content-info-box">
									<div class="ccb-is-pro__content-info-box-list">
										<span>
											<img src="<?php echo esc_attr( esc_url( CALC_URL . '/frontend/dist/img/check-mark.png' ) ); ?>" alt="check mark svg" width="20" height="20">
											<span><?php esc_html_e( 'Show custom confirmation pages when someone makes an order', 'cost-calculator-builder' ); ?></span>
										</span>
										<span>
											<img src="<?php echo esc_attr( esc_url( CALC_URL . '/frontend/dist/img/check-mark.png' ) ); ?>" alt="check mark svg" width="20" height="20">
											<span><?php esc_html_e( 'Put Confirmation Page right on the calculator page or as popup', 'cost-calculator-builder' ); ?></span>
										</span>
									</div>
								</div>
								<div class="ccb-is-pro__content-image-box">
									<img src="<?php echo esc_attr( esc_url( CALC_URL . '/frontend/dist/img/cf-page-pro.png' ) ); ?>" alt="cf-page-banner">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php else : ?>
		<?php do_action( 'render-thank-you-page' ); //phpcs:ignore ?>
	<?php endif; ?>
</div>
