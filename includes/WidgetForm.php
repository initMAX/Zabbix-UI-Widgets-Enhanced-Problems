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


namespace Modules\EnhancedProblems\Includes;

use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldAcknowledgeProblemStyle;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldColumnsList;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldDisplayStyle;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldFontFamily;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldFontStyle;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldSeveritiesCount;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldSortColumn;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldSortOrder;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldTagsList;
use Modules\EnhancedProblems\Type\AcknowledgeProblemStyleType;
use Modules\EnhancedProblems\Type\DisplayStyleType;
use Modules\EnhancedProblems\Type\FontFamilyType;
use Modules\EnhancedProblems\Type\SeveritiesCountType;
use Modules\EnhancedProblems\Type\SortColumnType;
use Modules\EnhancedProblems\Type\SortOrderType;
use Zabbix\Widgets\CWidgetField;
use Zabbix\Widgets\CWidgetForm;
use Zabbix\Widgets\Fields\CWidgetFieldCheckBox;
use Zabbix\Widgets\Fields\CWidgetFieldColor;
use Zabbix\Widgets\Fields\CWidgetFieldIntegerBox;
use Zabbix\Widgets\Fields\CWidgetFieldMultiSelectGroup;
use Zabbix\Widgets\Fields\CWidgetFieldMultiSelectHost;
use Zabbix\Widgets\Fields\CWidgetFieldRadioButtonList;
use Zabbix\Widgets\Fields\CWidgetFieldSelect;
use Zabbix\Widgets\Fields\CWidgetFieldSeverities;
use Zabbix\Widgets\Fields\CWidgetFieldTags;
use Zabbix\Widgets\Fields\CWidgetFieldTextBox;

/**
 * Enhanced problems widget form.
 */
class WidgetForm extends CWidgetForm
{
	public function addFields(): self {
		// Widget title section
		$this
			->addField(
				(new CWidgetFieldCheckBox('show_widget_title', _('Show Widget Title')))
			)
		;

		$this
			->addField(
				(new CWidgetFieldTextBox('widget_title_text', _('Text')))
					->setFlags(CWidgetField::FLAG_LABEL_ASTERISK)
			)
			->addField(
				(new CWidgetFieldTextBox('widget_title_link', _('Text - link')))
					->setFlags(CWidgetField::FLAG_LABEL_ASTERISK)
			)
			->addField(
				(new CWidgetFieldFontFamily('widget_title_font', _('Font family')))
					->setDefault(FontFamilyType::TREBUCHET->value)
			)
			->addField(
				(new CWidgetFieldIntegerBox('widget_title_font_size', _('Font size')))
					->setDefault(12)
			)
			->addField(
				(new CWidgetFieldColor('widget_title_font_color', _('Font color')))
			)
			->addField(
				(new CWidgetFieldFontStyle('widget_title_font_style', _('Font style')))
			)
			->addField(
				(new CWidgetFieldColor('widget_title_background_color', _('Background color')))
			)
		;

		// Filtering section
		$this
			->addField(
				new CWidgetFieldSeverities('severities', _('Severity'))
			)
			->addField(
				(new CWidgetFieldRadioButtonList('evaltype', _('Tags'), [
					TAG_EVAL_TYPE_AND_OR => _('And/Or'),
					TAG_EVAL_TYPE_OR => _('Or')
				]))->setDefault(TAG_EVAL_TYPE_AND_OR)
			)
			->addField(
				new CWidgetFieldTags('tags')
			)
			->addField(
				new CWidgetFieldCheckBox('host_based_filtering', _('Host based filtering'))
			)
			->addField(
				new CWidgetFieldMultiSelectGroup('groupids', _('Host groups'))
			)
			->addField(
				new CWidgetFieldMultiSelectGroup('exclude_groupids', _('Exclude host groups'))
			)
			->addField(
				new CWidgetFieldMultiSelectHost('hostids', _('Hosts'))
			)
			->addField(
				new CWidgetFieldTextBox('problem', _('Problem'))
			)
			->addField(
				(new CWidgetFieldCheckBox('show_unacknowledged_only', _('Show unacknowledged only')))
					//->setFlags(CWidgetField::FLAG_ACKNOWLEDGES)
			)
			->addField(
				new CWidgetFieldCheckBox('show_suppressed', _('Show suppressed problems'))
			)
		;
			
		// Tags list section	
		$this
			->addField(
				new CWidgetFieldTagsList('taglist')
			)
		;

		// Sorting section
		$this
			->addField(
				(new CWidgetFieldSortColumn('first_level_sorting_column', _('First level sorting column')))
					->setDefault(SortColumnType::FIRST_OCCURRENCE->value)
			)
			->addField(
				(new CWidgetFieldSortOrder('first_level_sorting_order', _('First level sorting order')))
					->setDefault(SortOrderType::ASC->value)
			)
			->addField(
				(new CWidgetFieldCheckBox('second_level_sorting_enabled', _('Second level sorting enabled')))
					->setDefault(0)
			)
			->addField(
				(new CWidgetFieldSortColumn('second_level_sorting_column', _('Second level sorting column')))
					->setDefault(SortColumnType::FIRST_OCCURRENCE->value)
			)
			->addField(
				(new CWidgetFieldSortOrder('second_level_sorting_order', _('Second level sorting order')))
					->setDefault(SortOrderType::ASC->value)
			)
			->addField(
				(new CWidgetFieldCheckBox('third_level_sorting_enabled', _('Third level sorting enabled')))
					->setDefault(0)
			)
			->addField(
				(new CWidgetFieldSortColumn('third_level_sorting_column', _('Third level sorting column')))
					->setDefault(SortColumnType::FIRST_OCCURRENCE->value)
			)
			->addField(
				(new CWidgetFieldSortOrder('third_level_sorting_order', _('Third level sorting order')))
					->setDefault(SortOrderType::ASC->value)
			)
		;

		// Display options section
		$this
			->addField(
				(new CWidgetFieldIntegerBox('show_lines', _('Maximum displayed alarm count'),
					min: ZBX_MIN_WIDGET_LINES,
					max: 10000,
				))
					->setDefault(1000)
					//->setFlags(CWidgetField::FLAG_LABEL_ASTERISK)
			)
			->addField(
				(new CWidgetFieldCheckBox('show_columns_header', _('Show header')))
					->setDefault(1)
			)
			->addField(
				(new CWidgetFieldDisplayStyle('display_mode', _('Display mode')))
					->setDefault(DisplayStyleType::__default->value)
			)
			->addField(
				(new CWidgetFieldAcknowledgeProblemStyle('acknowledge_problem_style', _('Acknowledge problem style')))
					->setDefault(AcknowledgeProblemStyleType::__default->value)
			)
			->addField(
				(new CWidgetFieldFontFamily('font', _('Font')))
					->setDefault(FontFamilyType::TREBUCHET->value)
			)
			->addField(
				(new CWidgetFieldIntegerBox(
					name: 'font_size', 
					label: _('Font size'),
					min: 8,
					max: 50,
				))
					->setDefault(10)
					->setFlags(CWidgetField::FLAG_LABEL_ASTERISK)
			)
			->addField(
				(new CWidgetFieldColor(
					name: 'font_color', 
					label: _('Font color')
				))
			)
			->addField(
				(new CWidgetFieldColor(
					name: 'background_color', 
					label: _('Background color')
				))
			)
			->addField(
				(new CWidgetFieldCheckBox('show_horizontal_scrollbar', _('Show horizontal scrollbar')))
					->setDefault(1)
			)
			->addField(
				(new CWidgetFieldCheckBox('show_vertical_scrollbar', _('Show vertical scrollbar')))
					->setDefault(1)
			)
		;

		// Summary row section
		$this
			->addField(
				(new CWidgetFieldCheckBox('show_summary_row', _('Show summary row')))
					->setDefault(1)
			)
		;

		$this
			->addField(
				(new CWidgetFieldTextBox('summary_row_name', _('Summary row name')))
			)
			->addField(
				(new CWidgetFieldTextBox('summary_row_link', _('Summary row link')))
			)
			->addField(
				(new CWidgetFieldSeveritiesCount('summary_row_show_severities_count', _('Show severities count')))
					->setDefault(SeveritiesCountType::NON_ACK_ONLY->value)
			)
			->addField(
				(new CWidgetFieldSeveritiesCount('summary_row_show_severities_total_count', _('Show severities total count')))
					->setDefault(SeveritiesCountType::NON_ACK_ONLY->value)
			)
		;

		// Columns selection and order section
		$this
			->addField(
				(new CWidgetFieldColumnsList('columnlist', _('Columns')))
			)
		;

		return $this;
	}

	protected function normalizeValues(array $values): array
	{
		$values = parent::normalizeValues($values);

		if (array_key_exists('sortorder', $values)) {
			foreach ($values['sortorder'] as $key => $sortorder) {
				if (!array_key_exists($key, $values)) {
					continue;
				}

				$sorted = [];

				foreach ($sortorder as $index) {
					$sorted[] = $values[$key][$index];
				}

				$values[$key] = $sorted;
			}
		}

		return $values;
	}
}
