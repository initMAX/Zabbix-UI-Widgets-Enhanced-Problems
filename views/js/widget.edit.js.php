<?php declare(strict_types = 0);
/*
** Copyright (C) 2021-2026 initMAX s.r.o.
**
** This program is free software: you can redistribute it and/or modify it under the terms of
** the GNU Affero General Public License as published by the Free Software Foundation, version 3.
**
** This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
** without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
** See the GNU Affero General Public License for more details.
**
** You should have received a copy of the GNU Affero General Public License along with this program.
** If not, see <https://www.gnu.org/licenses/>.
**/


?>//<script>

window.widget_enhancedproblems_form = new class {
	#form;
	#fields;
	#templates;

	init({values}) {
		this.values = values;
		this.#form = document.getElementById('widget-dialogue-form');
		this.#templates = {
		 	columns: new Template(document.querySelector('template#columns-template').innerHTML)
		}

		this.#initFields();
	}

	#initFields() {
		this.#fields = {
			tagslist: this.#form.querySelector('#tags_table_taglist'),
			columns: this.#form.querySelector('#list_columnlist'),
			hostBasedFiltering: this.#form.querySelector('[id="host_based_filtering"]'),
			secondLevelSortingEnabled: this.#form.querySelector('#second_level_sorting_enabled'),
			secondLevelSortingColumn: this.#form.querySelector('#second_level_sorting_column'),
			secondLevelSortingOrder: this.#form.querySelector('#second_level_sorting_order'),
			thirdLevelSortingEnabled: this.#form.querySelector('#third_level_sorting_enabled'),
			thirdLevelSortingColumn: this.#form.querySelector('#third_level_sorting_column'),
			thirdLevelSortingOrder: this.#form.querySelector('#third_level_sorting_order'),
		}

		this.#removeBuiltinFields();
		this.#initTagsList(this.#fields.tagslist);
		this.#initColorPickers();
		this.#initColumnsField(this.#fields.columns);
		this.#initToggleHostBasedFiltering(this.#fields.hostBasedFiltering);
		this.#initSortingFields();
	}

	#removeBuiltinFields() {
		const formFieldShowHeader = this.#form.querySelector('.form-field-show-header');
		const rfRateLabel = this.#form.querySelector('label[for="label-rf_rate"]');
		const rfRateInput = rfRateLabel.nextSibling;

		//formFieldShowHeader.remove();
		rfRateLabel.remove();
		rfRateInput.remove();
	}

	#initTagsList(container) {
		$(container).on('afteradd.dynamicRows', this.#tagsRefresh.bind(this));
		$(container).on('afterremove.dynamicRows', this.#columnsRefresh.bind(this));
		container.addEventListener('change', this.#columnsRefresh.bind(this));
		this.#tagsRefresh();
	}

	#tagsRefresh() {
		const tagindex = $(this.#fields.tagslist).data('dynamicRows').counter - 1;
		const tag_column = this.#fields.columns.querySelector(`[name$="[tagindex]"][value="${tagindex}"]`);

		if (tag_column) {
			return;
		}

		this.#columnsAddRow ({
			tagindex,
			prefix: 'Show tag: ',
			name: '',
			width: 20,
			label: ''
		});
	}

	#initColorPickers() {
		for (const colorpicker of this.#form.querySelectorAll('.<?= ZBX_STYLE_COLOR_PICKER ?> input')) {
			$(colorpicker).colorpicker({
				appendTo: '.overlay-dialogue-body',
				use_default: true,
				onUpdate: window.setIndicatorColor
			});
		}

	}

	#initColumnsField(container) {
		$(container).sortable({
			disabled: false,
			items: 'tbody tr.sortable',
			axis: 'y',
			containment: 'parent',
			cursor: 'grabbing',
			handle: 'div.<?= ZBX_STYLE_DRAG_ICON ?>',
			tolerance: 'pointer',
			opacity: 0.6,
			helper: function(e, ui) {
				for (let td of ui.find('>td')) {
					let $td = $(td);
					$td.attr('width', $td.width())
				}

				return ui;
			},
			stop: function(e, ui) {
				ui.item.find('>td').removeAttr('width');
				ui.item.removeAttr('style');
			},
			start: function(e, ui) {
				$(ui.placeholder).height($(ui.helper).height());
			}
		});
		container.addEventListener('change', this.#columnsRefresh.bind(this));
		this.#columnsRefresh();
	}

	#columnsRefresh() {
		let total = 0;

		this.#fields.columns.querySelectorAll('tr.sortable')
			.forEach(tr => {
				const tagindex = parseInt(tr.querySelector('[name$="[tagindex]"]').value, 10);
				const readonly = !tr.querySelector('[type="checkbox"][name$="[enabled]"]').checked;
				const width = readonly ? 0 : parseInt(tr.querySelector('[type="text"][name$="[width]"]').value, 10);
				tr.querySelectorAll('input[type="text"]').forEach(input => input.toggleAttribute('readonly', readonly));

				if (tagindex >= 0) {
					const taginput = this.#fields.tagslist.querySelector(`[type="text"][name$="[${tagindex}][tag]"]`);

					if (taginput === null) {
						tr.remove();
						return;
					}

					tr.querySelector('.js-columns-name').innerText = taginput.value;
					tr.querySelector('[name$="[name]"]').value = taginput.value;
				}

				total += isNaN(width) ? 0 : width;
			});

		this.#fields.columns.querySelector('.js-columns-total').innerText = total;
	}

	#columnsAddRow(row) {
		const trs = this.#fields.columns.querySelectorAll('.sortable');
		const after = trs[trs.length - 1];
		const template = document.createElement('template');

		template.innerHTML = this.#templates.columns.evaluate({...row, rowNum: trs.length});
		template.content.firstChild.querySelectorAll('input[type="text"]')
			.forEach(input => input.toggleAttribute('readonly', true));
		after.parentNode.insertBefore(template.content.firstChild, after.nextSibling);
	}

	#initToggleHostBasedFiltering(toggleElement) {
		toggleElement.addEventListener('change', this.#toggleHostBasedFiltering.bind(this, this.#fields.hostBasedFiltering));
		
		this.#toggleHostBasedFiltering(toggleElement);
	}

	#toggleHostBasedFiltering(toggleElement) {
		const hostBasedFilteringLabels = [
			this.#form.querySelector('[for="groupids__ms"]'),
			this.#form.querySelector('[for="hostids__ms"]'),
			this.#form.querySelector('[for="exclude_groupids__ms"]'),
		];

		hostBasedFilteringLabels.forEach(label => {
			label.style.display = toggleElement.checked ? 'block' : 'none';
			label.nextSibling.style.display = toggleElement.checked ? 'block' : 'none';
		});
	}

	#initSortingFields() {
		this.#fields.secondLevelSortingEnabled.addEventListener('change', this.#toggleSecondLevelSortingFields.bind(this));
		this.#fields.thirdLevelSortingEnabled.addEventListener('change', this.#toggleThirdLevelSortingFields.bind(this));

		this.#toggleSecondLevelSortingFields();
		this.#toggleThirdLevelSortingFields();
	}

	#toggleSecondLevelSortingFields() {
		this.#fields.secondLevelSortingColumn.disabled = !this.#fields.secondLevelSortingEnabled.checked;
		this.#fields.secondLevelSortingOrder.querySelectorAll('input').forEach(input => {
			input.disabled = !this.#fields.secondLevelSortingEnabled.checked;
		});

		this.#fields.thirdLevelSortingEnabled.disabled = !this.#fields.secondLevelSortingEnabled.checked;
		if (!this.#fields.secondLevelSortingEnabled.checked) {
			this.#fields.thirdLevelSortingEnabled.checked = false;
			this.#toggleThirdLevelSortingFields();
		}
	}

	#toggleThirdLevelSortingFields() {
		this.#fields.thirdLevelSortingColumn.disabled = !this.#fields.thirdLevelSortingEnabled.checked;
		this.#fields.thirdLevelSortingOrder.querySelectorAll('input').forEach(input =>
			input.disabled = !this.#fields.thirdLevelSortingEnabled.checked
		);
	}
};
