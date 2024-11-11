<?php
$default_img      = CALC_URL . '/frontend/dist/img/default.png';
$general_settings = get_option( 'ccb_general_settings' );
$get_date_format  = get_option( 'date_format' );
?>

<calc-builder-front :key="getFieldsKey" :custom="1" :content="{...preview_data, default_img: '<?php echo esc_attr( $default_img ); ?>'}" inline-template :id="getId">
	<div :class="'ccb-wrapper-' + getId">
		<div ref="calc" :class="['calc-container', {[boxStyle]: preview !== 'mobile'}]" :data-calc-id="getId">
			<loader v-if="loader"></loader>
			<template>
				<div class="calc-fields calc-list calc-list__indexed" :class="{loaded: !loader, 'payment' :  getHideCalc}" v-if="!loader">
					<div class="calc-list-inner">
						<div class="calc-item-title">
							<div class="ccb-calc-heading">{{ getTitle }}</div>
						</div>
						<div v-if="calc_data" class="calc-fields-container">
							<template v-for="field in calc_data.fields">
								<template v-if="field && field.alias && field.type !== 'Total'">
									<component
											text-days="<?php esc_attr_e( 'days', 'cost-calculator-builder' ); ?>"
											v-if="fields[field.alias]"
											:is="field._tag"
											:id="calc_data.id"
											:field="field"
											:is_preview="true"
											v-model="fields[field.alias].value"
											v-on:[field._event]="change"
											v-on:condition-apply="renderCondition"
											format="<?php esc_attr( $get_date_format ); ?>"
											:converter="currencyFormat"
											:disabled="fields[field.alias].disabled"
											v-on:change="change"
											:key="!field.hasNextTick ? field.alias : field.alias + '_' + fields[field.alias].nextTickCount"
									>
									</component>
								</template>
								<template v-else-if="field && !field.alias && field.type !== 'Total'">
									<component
											:id="calc_data.id"
											style="boxStyle"
											:is="field._tag"
											:field="field"
									>
									</component>
								</template>
							</template>
						</div>
					</div>
				</div>
				<div class="calc-subtotal calc-list" :id="getStickyData" :class="{loaded: !loader}">
					<div class="calc-list-inner">
						<div class="calc-item-title calc-accordion">
							<div class="ccb-calc-heading">{{ getHeaderTitle }}</div>
							<template v-if="">

							</template>
							<span class="calc-accordion-btn" ref="calcAccordionToggle" @click="toggleAccordion" :style="{display: settings.general && settings.general.descriptions ? 'flex': 'none'}">
								<i class="ccb-icon-Path-3485" :style="{transform: currentAccordionHeight === '0px' ? 'rotate(0)' : 'rotate(180deg)'}"></i>
							</span>
						</div>
						<div class="calc-subtotal-list" :class="{ 'show-unit': this.showUnitInSummary }">
							<div class="calc-subtotal-list-accordion" ref="calcAccordion" :style="{maxHeight: currentAccordionHeight}">
								<div class="calc-subtotal-list-header" v-if="this.showUnitInSummary">
									<span class="calc-subtotal-list-header__name"><?php esc_html_e( 'Name', 'cost-calculator-builder' ); ?></span>

									<span class="calc-subtotal-list-header__value"><?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?></span>
								</div>
								<template v-for="field in getTotalSummaryFields" v-if="field.alias.indexOf('total') === -1 && settings && settings.general.descriptions">
									<div v-if="'text' == field.alias.replace(/\_field_id.*/,'')" :class="[field.alias, 'sub-list-item']" :style="{display: field.hidden ? 'none' : 'flex'}" >
										<span class="sub-item-title">{{ field.label }}</span>
										<span class="sub-item-space" :class="[{'text-empty-field': field.value == 0}]"></span>
										<span class="sub-item-value" v-if="field.value != 0"> {{ field.converted }} </span>
									</div>
									<div v-else-if="'datePicker' == field.alias.replace(/\_field_id.*/,'')" :class="[field.alias, 'sub-list-item']" :break-border="breakBorder(field)" :style="{display: field.hidden ? 'none' : 'flex'}">
										<span class="sub-item-title">{{ field.label }}</span>
										<span class="sub-item-space"></span>
										<span class="sub-item-value" v-if="!field.summary_view || field.summary_view === 'show_value'"> {{ field.convertedPrice }} </span>
										<span class="sub-item-value" v-if="field.summary_view !== 'show_value' && field.extraView"> {{  field.extraView }} </span>
										<span class="sub-item-value" v-if="field.summary_view !== 'show_value' && !field.extraView && this.showUnitInSummary"></span>
									</div>
									<div v-else :class="[field.alias, 'sub-list-item']" :style="{display: field.hidden ? 'none' : 'flex'}" :break-border="breakBorder(field)">
										<span class="sub-item-title">{{ field.label }}</span>
										<span class="sub-item-space"></span>
										<span class="sub-item-value" v-if="!field.summary_view || field.summary_view === 'show_value'"> {{  field.converted }} </span>
										<span class="sub-item-value" v-if="field.summary_view !== 'show_value' && field.extraView"> {{  field.extraView }} </span>
										<span class="sub-item-value" v-if="field.summary_view !== 'show_value' && !field.extraView && this.showUnitInSummary"> </span>
									</div>
									<div :class="[field.alias, 'sub-list-item inner']" :style="{display: field.hidden ? 'none' : ''}" v-if="field.option_unit">
										<div class="sub-inner" :style="[{display: field.hidden ? 'none' : 'flex'}, {flexDirection: 'column'}]">
											<span class="sub-item-unit" v-if="field.option_unit_info">{{ field.option_unit_info }}</span>
											<span class="sub-item-unit" :class="{'break-all': field.break_all}">{{ field.option_unit }}</span>
										</div>
									</div>
									<div :class="[field.alias, 'sub-list-item inner']" v-if="field.options && field.options.length && ['checkbox', 'toggle', 'checkbox_with_img'].includes(field.alias.replace(/\_field_id.*/,''))">
										<div class="sub-inner" v-for="option in field.options" :style="{display: field.hidden ? 'none' : 'flex'}">
											<span class="sub-item-title"> {{ option.label }} </span>
											<span class="sub-item-space"></span>
											<span class="sub-item-value"> {{ option.converted }} </span>
										</div>
									</div>
								</template>
							</div>
						</div>
						<div class="calc-subtotal-list" style="margin-top: 20px; padding-top: 10px;" ref="calcTotals">
							<template v-for="item in formulaConst">
								<div v-if="formulaConst.length === 1 && typeof formulaConst[0].alias === 'undefined'" style="display: flex" class="sub-list-item total">
									<span class="sub-item-title"><?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?></span>
									<span class="sub-item-value">{{ item.data.converted }}</span>
								</div>
								<cost-total v-else :value="item.total" :field="item.data" :id="calc_data.id" v-on:condition-apply="renderCondition"></cost-total>
							</template>
						</div>
						<div class="calc-subtotal-list">
							<?php if ( ccb_pro_active() ) : ?>
								<cost-pro-features inline-template :settings="content.settings">
									<?php echo \cBuilder\Classes\CCBProTemplate::load( 'frontend/pro-features', array( 'settings' => array(), 'general_settings' => array(), 'invoice' => $general_settings['invoice'] ) ); // phpcs:ignore ?>
								</cost-pro-features>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</template>
		</div>
	</div>
</calc-builder-front>
