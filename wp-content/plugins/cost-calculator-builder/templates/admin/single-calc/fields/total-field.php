<div class="cbb-edit-field-container">
	<div class="ccb-edit-field-header">
		<span class="ccb-edit-field-title ccb-heading-3 ccb-bold"><?php esc_html_e( 'Formula', 'cost-calculator-builder' ); ?></span>
		<div class="ccb-field-actions">
			<button class="ccb-button default" @click="$emit( 'cancel' )"><?php esc_html_e( 'Cancel', 'cost-calculator-builder' ); ?></button>
			<button class="ccb-button success" @click.prevent="saveWithValidation"><?php esc_html_e( 'Save', 'cost-calculator-builder' ); ?></button>
		</div>
	</div>
	<div class="ccb-grid-box">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="ccb-edit-field-switch">
						<div class="ccb-edit-field-switch-item ccb-default-title" :class="{active: tab === 'element'}" @click="tab = 'element'">
							<?php esc_html_e( 'Element', 'cost-calculator-builder' ); ?>
						</div>
						<div class="ccb-edit-field-switch-item ccb-default-title" :class="{active: tab === 'settings'}" @click="tab = 'settings'">
							<?php esc_html_e( 'Settings', 'cost-calculator-builder' ); ?>
							<span class="ccb-fields-required" v-if="errorsCount > 0">{{ errorsCount }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="container" v-show="tab === 'element'">
			<div class="row ccb-p-t-20">
				<div class="col-12">
					<div class="ccb-input-wrapper">
						<span class="ccb-input-label"><?php esc_html_e( 'Title', 'cost-calculator-builder' ); ?></span>
						<input type="text" class="ccb-heading-5 ccb-light" v-model.trim="totalField.label" placeholder="<?php esc_attr_e( 'Enter field name', 'cost-calculator-builder' ); ?>">
					</div>
				</div>
			</div>
			<div class="row ccb-p-t-15" v-if="formulaErrorMessage.length > 0">
				<div class="col-12">
					<div class="ccb-formula-message-errors">
						<p class="ccb-formula-error-message" v-for="(item, index) in formulaErrorMessage">
							{{item.message}}	
						</p>
					</div>	
				</div>
			</div>
			<div class="row ccb-p-t-15" v-show="totalField.formulaView">
				<div class="col-12">
					<div class="ccb-edit-field-aliases">
						<template v-if="available.length">
							<div class="ccb-edit-field-alias" v-for="(item, index) in available_fields" @click="insertAtCursor(item.type === 'Total' ? '(' + item.alias + ')' : item.alias)" v-if="item.alias != 'total'">
								{{ item.alias }}
							</div>
						</template>
						<template v-else>
							<p><?php esc_html_e( 'No Available fields yet!', 'cost-calculator-builder' ); ?></p>
						</template>
					</div>
				</div>
			</div>
			<div class="row ccb-p-t-10">
				<div class="col-12">
					<div class="ccb-edit-field-formula">
						<div class="ccb-formula-content" v-show="totalField.formulaView">
							<div class="ccb-input-wrapper">
								<textarea class="ccb-heading-5 ccb-light" v-model="totalField.costCalcFormula" :ref="'ccb-formula-' + totalField._id" placeholder="<?php esc_attr_e( 'Enter your formula', 'cost-calculator-builder' ); ?>"></textarea>
							</div>
						</div>
						<div class="ccb-formula-content"  v-show="!totalField.formulaView">
							<!-- <div class="ccb-input-wrapper">
								<div tabindex="0" @keydown="handleKeyDown"  :ref="'ccb-formula-letter-' + totalField._id" class="ccb-formula-wrapper" @click="insertCursorAtClick" placeholder="<?php esc_attr_e( 'Set Additional Classes', 'cost-calculator-builder' ); ?>">	
								<span :class="[{'ccb-formula-content-empty formula-cursor cursor-right':formulaElements.length === 0 && !cursorIsHidden}, {'ccb-formula-content-no-cursor-empty': cursorIsHidden}]" v-if="formulaElements.length === 0"></span>		
								<template>
									<div>
										<pre v-for="(line, lineIndex) in domRenderElements" :key="lineIndex">
											<span v-for="(item, itemIndex) in line" :key="itemIndex">
											<span @click="moveCursor(itemIndex, $event)" :key="itemIndex" :class="['ccb-formula-item', item.className, { [cursorPositionClass]: itemIndex === activeIndex && !cursorIsHidden }]"
											:style="{color: item.color}">{{ item.content }}</span>
											</span>
										</pre>
									</div>
								</template>
								<template v-for="(element, index) in formulaElements">
									<span
										@click="moveCursor(index, $event)"
										:key="index"
										:class="['ccb-formula-item', element.className, { [cursorPositionClass]: index === activeIndex && !cursorIsHidden }]"
										:style="{ color: element.color }"
										v-if="element.type !== 'indent'"
									>{{ element.content }}</span>
									<div :class="['ccb-formula-field-newline', { [cursorPositionClass]: index === activeIndex && !cursorIsHidden }]" @click="moveCursor(index, $event)" :key="index" v-else></div>
								</template>
								<span
										@click="moveCursor(index, $event)"
										v-for="(element, index) in formulaElements"
										:key="index"
										:class="['ccb-formula-item', element.className, { [cursorPositionClass]: index === activeIndex && !cursorIsHidden }]"
										:style="{color: element.color}"
									>{{element.content}}</span>
									
								</div>
							</div> -->
							<div class="ccb-cm-formula-input" spellcheck="false">
								
							</div>
						</div>

						<div class="ccb-formula-tools" v-show="totalField.formulaView">
							<span class="ccb-formula-tool" title="Addition (+)" @click="insertAtCursor('+')">
								<span class="plus">+</span>
							</span>
							<span class="ccb-formula-tool" title="Subtraction (-)" @click="insertAtCursor('-')">-</span>
							<span class="ccb-formula-tool" title="Division (/)" @click="insertAtCursor('/')">/</span>
							<span class="ccb-formula-tool" title="Remainder (%)" @click="insertAtCursor('%')">%</span>
							<span class="ccb-formula-tool" title="Multiplication (*)" @click="insertAtCursor('*')">
								<span class="multiple">*</span>
							</span>
							<span class="ccb-formula-tool" title="Math.pow(x, y) returns the value of x to the power of y:" @click="insertAtCursor('Math.pow(')">pow</span>
							<span class="ccb-formula-tool" title="Math.sqrt(x) returns the square root of x:" @click="insertAtCursor('Math.sqrt(')">sqrt</span>
							<span class="ccb-formula-tool" title="Math.abs(x)" @click="insertAtCursor('Math.abs(')">abs</span>
							<span class="ccb-formula-tool" title="Math.round(x) returns the value of x rounded to its nearest integer:" @click="insertAtCursor('Math.round(')">round</span>
							<span class="ccb-formula-tool" title="Math.ceil(x) returns the value of x rounded up to its nearest integer:" @click="insertAtCursor('Math.ceil(')">ceil</span>
							<span class="ccb-formula-tool" title="Math.floor(x) returns the value of x rounded down to its nearest integer:" @click="insertAtCursor('Math.floor(')">floor</span>
						</div>
						<div class="ccb-formula-tools" v-show="!totalField.formulaView">
							<span class="ccb-formula-tool" title="Addition (+)" @click="insertAtCursorDom('+')">
								<span class="plus">+</span>
							</span>
							<span class="ccb-formula-tool" title="Subtraction (-)" @click="insertAtCursorDom('-')">-</span>
							<span class="ccb-formula-tool" title="Division (/)" @click="insertAtCursorDom('/')">/</span>
							<span class="ccb-formula-tool" title="Remainder (%)" @click="insertAtCursorDom('%')">%</span>
							<span class="ccb-formula-tool" title="Multiplication (*)" @click="insertAtCursorDom('*')">
								<span class="multiple">*</span>
							</span>
							<span class="ccb-formula-tool" title="Open bracket '('" @click="insertAtCursorDom('(')">(</span>
							<span class="ccb-formula-tool" title="Close bracket ')'" @click="insertAtCursorDom(')')">)</span>
							<span class="ccb-formula-tool" title="Math.pow(x, y) returns the value of x to the power of y:" @click="insertAtCursorDom(' POW(,) ')">^</span>
							<span class="ccb-formula-tool" title="Math.sqrt(x) returns the square root of x:" @click="insertAtCursorDom(' SQRT() ')">&#8730;</span>
							<span class="ccb-formula-tool" title="If operator" @click="insertAtCursorDom('IF(){}')">IF</span>
							<span class="ccb-formula-tool" title="If else operator" @click="insertAtCursorDom(' IF(){}ELSE{} ')">IF ELSE</span>
							<span class="ccb-formula-tool" title="Boolean operator &&" @click="insertAtCursorDom(' AND ')">AND</span>
							<span class="ccb-formula-tool" title="Boolean operator ||" @click="insertAtCursorDom(' OR ')">OR</span>
							<span class="ccb-formula-tool" title="Operator less than" @click="insertAtCursorDom('<')"><</span>
							<span class="ccb-formula-tool" title="Operator more than" @click="insertAtCursorDom('>')">></span>
							<span class="ccb-formula-tool" title="Operator less than" @click="insertAtCursorDom('<=')"><=</span>
							<span class="ccb-formula-tool" title="Operator more than" @click="insertAtCursorDom('>=')">>=</span>
							<span class="ccb-formula-tool" title="Operator not equal" @click="insertAtCursorDom('!=')">!=</span>
							<span class="ccb-formula-tool" title="Operator strict equal" @click="insertAtCursorDom('==')">==</span>
							<span class="ccb-formula-tool" title="Math.abs(x)" @click="insertAtCursorDom(' ABS()')">ABS</span>
							<span class="ccb-formula-tool" title="Math.round(x) returns the value of x rounded to its nearest integer:" @click="insertAtCursorDom(' ROUND()')">ROUND</span>
							<span class="ccb-formula-tool" title="Math.ceil(x) returns the value of x rounded up to its nearest integer:" @click="insertAtCursorDom(' CEIL() ')">CEIL</span>
							<span class="ccb-formula-tool" title="Math.floor(x) returns the value of x rounded down to its nearest integer:" @click="insertAtCursorDom(' FLOOR()')">FLOOR</span>
						</div>
						<div class="ccb-edit-field-aliases" v-show="!totalField.formulaView">
							<template v-if="available.length">
									<div class="ccb-edit-field-alias" v-for="(item, index) in available_fields" :title="item.alias" @click="insertAtCursorDom( ' '+item.letter+' ')" v-if="item.alias != 'total'">
									<div class="ccb-edit-field-letter">
										<span>{{ item.letter }}</span>		
									</div>
									<div class="ccb-edit-field-label">
										<span>{{ item.label }}</span>		
									</div>
								</div>
							</template>
							<template v-else>
								<p><?php esc_html_e( 'No Available fields yet!', 'cost-calculator-builder' ); ?></p>
							</template>
						</div>
					</div>
				</div>
			</div>

			<div class="row ccb-p-t-15">
				<div class="col-12">
					<a class="ccb-documentation-link" href="https://docs.stylemixthemes.com/cost-calculator-builder/calculator-elements/total" target=”_blank”>
						<?php esc_html_e( 'How does it work?', 'cost-calculator-builder' ); ?>
					</a>
				</div>
			</div>
		</div>
		<div class="container" v-show="tab === 'settings'">
			<div class="row ccb-p-t-20">
				<div class="col-6">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox" v-model="totalField.totalSymbol"/>
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Show Alternative Symbol', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
				<div class="col-6">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox"  v-model="totalField.hidden"/>
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Hidden by Default', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
				<div class="col-6 ccb-p-t-15">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox" v-model="totalField.formulaView" />
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Show the legacy formula view ', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
			</div>
			<div class="row ccb-p-t-15" v-if="totalField.totalSymbol">
				<div class="col-12">
					<div class="ccb-input-wrapper">
						<span class="ccb-input-label"><?php esc_html_e( 'Alternative Symbol', 'cost-calculator-builder' ); ?></span>
						<input type="text" class="ccb-heading-5 ccb-light" v-model="totalField.totalSymbolSign" placeholder="<?php esc_attr_e( 'Set Alternative Symbol...', 'cost-calculator-builder' ); ?>">
					</div>
				</div>
			</div>
			<div class="row ccb-p-t-15">
				<div class="col-12">
					<div class="ccb-input-wrapper">
						<span class="ccb-input-label"><?php esc_html_e( 'Additional Classes', 'cost-calculator-builder' ); ?></span>
						<textarea class="ccb-heading-5 ccb-light" v-model="totalField.additionalStyles" placeholder="<?php esc_attr_e( 'Set Additional Classes', 'cost-calculator-builder' ); ?>"></textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
