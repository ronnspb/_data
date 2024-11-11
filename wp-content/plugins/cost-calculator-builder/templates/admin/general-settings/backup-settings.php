<div class="ccb-tab-container">
	<?php if ( ! defined( 'CCB_PRO' ) ) : ?>
		<div class="ccb-grid-box" style="display: flex; justify-content: space-between">
			<div class="container">
				<div class="row ccb-p-t-15">
					<div class="col-12">
						<span class="ccb-tab-title"><?php esc_html_e( 'Backup settings', 'cost-calculator-builder-pro' ); ?></span>
					</div>
				</div>
				<div class="row ccb-p-t-15">
					<div class="col-12 ccb-p-t-5">
						<span class="ccb-tab-subtitle" style="font-weight: 700">🔒<?php esc_html_e( ' Available in PRO version', 'cost-calculator-builder-pro' ); ?></span>
					</div>
					<div class="col-12">
						<span class="ccb-tab-subtitle"><?php esc_html_e( 'You can restore  up to last 3 saved  changes, Each save creates a backup', 'cost-calculator-builder-pro' ); ?></span>
					</div>
					<div class="col-3 ccb-p-t-15">
						<div style="display: flex; height: 40px">
							<a href="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=calcwpadmin&utm_medium=freetoprobutton&utm_campaign=backup_settings" target="_blank" class="ccb-button ccb-href success"><?php esc_html_e( 'Upgrade Now', 'cost-calculator-builder' ); ?></a>
						</div>
					</div>
				</div>
			</div>
			<div class="ccb-image-box">
				<img src="<?php echo esc_url( CALC_URL . '/frontend/dist/img/backup-pro.png' ); ?>">
			</div>
		</div>
	<?php else : ?>
		<?php do_action( 'render-backup-settings' ); //phpcs:ignore ?>
	<?php endif; ?>
</div>
