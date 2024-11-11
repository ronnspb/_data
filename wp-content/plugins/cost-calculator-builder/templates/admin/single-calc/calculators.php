<?php
$modal_types = array(
	'preview'       => array(
		'type' => 'preview',
		'path' => CALC_PATH . '/templates/admin/single-calc/modals/modal-preview.php',
	),
	'calc-settings' => array(
		'type' => 'calc-settings',
		'path' => CALC_PATH . '/templates/admin/single-calc/modals/calc-settings.php',
	),
	'quick-tour'    => array(
		'type' => 'quick-tour',
		'path' => CALC_PATH . '/templates/admin/single-calc/modals/quick-tour-start.php',
	),
	'history'       => array(
		'type' => 'history',
		'path' => CALC_PATH . '/templates/admin/single-calc/modals/history.php',
	),
);

?>
<div class="ccb-create-calc">
	<div class="ccb-field-overlay" @click="hideOverlay" v-if="getType !== null"></div>
	<div class="ccb-create-calc-sidebar ccb-custom-scrollbar calc-quick-tour-elements" :style="getCalcSidebarStyleForElementStyleTourStep['ccb-create-calc-sidebar']" >
		<div class="ccb-sidebar-header" :style="getCalcSidebarStyleForElementStyleTourStep['ccb-sidebar-header']">
			<span class="ccb-heading-5 ccb-bold"><?php esc_html_e( 'Elements', 'cost-calculator-builder' ); ?></span>
		</div>
		<?php echo \cBuilder\Classes\CCBTemplate::load( '/admin/single-calc/partials/sidebar-items' ); // phpcs:ignore ?>
	</div>
	<div class="ccb-create-calc-content">
		<div class="ccb-not-allowed ccb-create-calc-content-fields">
			<div class="ccb-fields-container ccb-hint-fields-container" :class="{'ccb-container-empty': $store.getters.getBuilder.length === 0}">
				<div class="ccb-fields-header">
					<div class="ccb-fields-header-box">
						<div class="ccb-fields-header-box-calculator-title">
							<span class="ccb-calc-title calc-quick-tour-title-box" v-if="!getEditVal">
								<span class="ccb-title" @click="getEditVal = true">{{ title }}</span>
								<i class="ccb-title-edit ccb-icon-Path-3483" @click="getEditVal = true"></i>
							</span>
							<span class="ccb-calc-title calc-quick-tour-title-box" v-else>
								<input type="text" class="ccb-title" v-model="title" @blur="editTitle">
								<i class="ccb-title-approve ccb-icon-Path-3484" @click="() => edit_title(false)"></i>
							</span>
						</div>
						<span class="ccb-default-description ccb-light"><?php esc_html_e( 'Drag and drop elements from sidebar here to create a calculator', 'cost-calculator-builder' ); ?></span>
					</div>
					<?php if ( defined( 'CALC_DEV_MODE' ) ) : ?>
						<button class="ccb-button success ccb-settings" @click="openTemplateSettings"><?php esc_html_e( 'Config', 'cost-calculator-builder' ); ?></button>
					<?php endif; ?>
				</div>
				<div class="ccb-fields-wrapper ccb-custom-scrollbar " :class="{'ccb-disable-scroll': $store.getters.getBuilder.length === 0}">
					<draggable
							@change="log"
							group="fields"
							:list="$store.getters.getBuilder"
							v-model="getFields"
							class="ccb-fields-item-row"
							draggable=".ccb-fields-drag-row"
							:key="draggableKey"
							v-bind="dragOptions"
					>
						<div class="ccb-fields-drag-row" v-for="(field, key) in getFields" :key="key" @click.stop="e => editField(e, field.type, key)">
							<div class="ccb-fields-item"  :class="{'ccb-field-selected': editId !== null && +editId === key && !getErrorIdx.includes(key), 'ccb-idx-error': getErrorIdx.includes(key)}">
								<div class="ccb-fields-item-left" v-if="field.type !== 'Html' && field.type !== 'Line'">
									<span class="ccb-field-item-title-box">
										<span>{{ field.label }}</span>
									</span>
									<span class="ccb-field-item-icon-box">
										<span class="ccb-field-item-icon">
											<i :class="field.icon"></i>
										</span>
										<span class="ccb-default-description ccb-light">
											{{ field.text }}
										</span>
										<span class="ccb-default-description ccb-light">
											{{ field.alias | to-format }}
										</span>
									</span>
								</div>
								<div class="ccb-fields-item-left" v-if="field.type === 'Line'">
									<span class="line-field"></span>
								</div>
								<div class="ccb-fields-item-left" v-if="field.type === 'Html'">
									<span class="html-field">
										<-- <?php esc_html_e( 'HTML element', 'cost-calculator-builder' ); ?> {{ field.alias | to-format }} -->
									</span>
								</div>
								<div class="ccb-fields-item-right">
									<span class="ccb-fields-item-icon drag-icon">
										<i class="ccb-icon-drag-dots"></i>
									</span>
									<span class="ccb-fields-item-icon border-icon" @click.prevent="duplicateField(field._id, !field.alias || field._id === null || field._id === undefined || duplicateNotAllowed)" v-if="!(!field.alias || field._id === null || field._id === undefined || duplicateNotAllowed)">
										<i class="ccb-icon-Path-3505"></i>
									</span>
									<span class="ccb-fields-item-icon border-icon" @click.prevent="showConfirm(key, field)">
										<i class="ccb-icon-Path-3503"></i>
									</span>
								</div>
							</div>
							<span class="ccb-idx-error-info" v-if="getErrorIdx.includes(key)">
								<?php esc_html_e( 'Please  add options', 'cost-calculator-builder' ); ?>
							</span>
						</div>
						<div class="ccb-fields-item ccb-place" :class="{'ccb-place-show': $store.getters.getBuilder.length === 0, 'ccb-container-show-quick-tour-elements': showElements}">
							<span><?php esc_html_e( 'Place element here', 'cost-calculator-builder' ); ?></span>
						</div>
					</draggable>
				</div>
			</div>
		</div>
		<div class="ccb-create-calc-content-edit-field ccb-custom-scrollbar calc-quick-tour-edit-field calc-quick-tour-element-styles" :class="{'has-content': getType}">
			<template v-if="getType">
				<?php
				$fields = \cBuilder\Helpers\CCBFieldsHelper::fields();
				?>
				<?php foreach ( $fields as $key => $field ) : ?>
					<component
							inline-template
							:key="updateEditKey"
							:field="fieldData"
							@save="addOrSaveField"
							:id="editId"
							:index="getIndex"
							:order="getOrderId"
							@cancel="closeOrCancelField"
							:available="$store.getters.getBuilder"
							is="<?php echo esc_attr( $field['type'] ); ?>-field"
							v-if="getType === '<?php echo esc_attr( $field['type'] ); ?>'"
					>
						<?php echo \cBuilder\Classes\CCBTemplate::load( '/admin/single-calc/fields/' . $field['type'] . '-field' ); // phpcs:ignore ?>
					</component>
				<?php endforeach; ?>
			</template>
			<template v-else>
				<div class="ccb-edit-field-no-selected">
					<div class="ccn-edit-no-selected-box">
						<span class="ccb-heading-3 ccb-bold"><?php esc_html_e( 'Click to See More', 'cost-calculator-builder' ); ?></span>
						<span class="ccb-default-title ccb-light-2" style="line-height: 1"><?php esc_html_e( 'Choose an element to configure the settings ', 'cost-calculator-builder' ); ?></span>
					</div>
				</div>
			</template>
		</div>
	</div>
	<ccb-confirm-popup
		v-if="confirmPopup"
		:status="confirmPopup"
		@close-confirm="removeFromBuilder"
		:field="currentFieldId"
		cancel="<?php esc_html_e( 'Cancel', 'cost-calculator-builder' ); ?>"
		del="<?php esc_html_e( 'Delete field', 'cost-calculator-builder' ); ?>"
	>
		<slot>
			<span slot="description"><?php esc_html_e( 'Are you sure you want to delete this field and all data associated with it?', 'cost-calculator-builder' ); ?></span>
		</slot>
	</ccb-confirm-popup>
	<ccb-modal-window>
		<template v-slot:content>
			<?php foreach ( $modal_types as $m_type ) : ?>
				<template v-if="$store.getters.getModalType === '<?php echo esc_attr( $m_type['type'] ); ?>'">
					<?php require_once $m_type['path']; ?>
				</template>
			<?php endforeach; ?>
		</template>
	</ccb-modal-window>
</div>
