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


use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldAcknowledgeProblemStyleView;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldColumnsListView;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldDisplayStyleView;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldFontFamilyView;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldFontStyleView;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldSeveritiesCountView;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldSortColumnView;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldSortOrderView;
use Modules\EnhancedProblems\Includes\Fields\CWidgetFieldTagsListView;


/**
 * Enhanced problems widget form view.
 *
 * @var CView $this
 * @var array $data
 */

$form = new CWidgetFormView($data);

$form
	->addField(
		new CWidgetFieldCheckBoxView($data['fields']['show_widget_title'])
	)
	->addFieldsGroup(getWidgetTitleFieldsViews($form, $data['fields']))
	->addFieldsGroup(getFilteringFieldsGroupViews($form, $data['fields']))
	->addFieldsGroup(getTagListFieldsGroupViews($form, $data['fields']))
	->addFieldsGroup(getSortingFieldsGroupViews($form, $data['fields']))
	->addFieldsGroup(getDisplayOptionsFieldsGroupViews($form, $data['fields']))
	->addField(
		new CWidgetFieldCheckBoxView($data['fields']['show_summary_row'])
	)
	->addFieldsGroup(getSummaryRowFieldsGroupViews($form, $data['fields']))
	->addFieldsGroup(getColumnsGroupViews($form, $data['fields']))
	->includeJsFile('js/widget.edit.js.php')
	->addJavaScript('widget_enhancedproblems_form.init(' . json_encode([
		'values' => array_combine(
						array_keys($data['fields']),
						array_map(function ($o) { return $o->getValue(); }, $data['fields'])
					),
	]) . ');')
	->show();

function getWidgetTitleFieldsViews(CWidgetFormView $form, array $fields): CWidgetFieldsGroupView {
	$widget_title_text = new CWidgetFieldTextBoxView($fields['widget_title_text']);
	$widget_title_link = new CWidgetFieldTextBoxView($fields['widget_title_link']);
	$widget_title_font = new CWidgetFieldFontFamilyView($fields['widget_title_font']);
	$widget_title_font_size = new CWidgetFieldIntegerBoxView($fields['widget_title_font_size']);
	$widget_title_font_color = new CWidgetFieldColorView($fields['widget_title_font_color']);
	$widget_title_font_style = new CWidgetFieldFontStyleView($fields['widget_title_font_style']);
	$widget_title_background_color = new CWidgetFieldColorView($fields['widget_title_background_color']);

	$result = new CWidgetFieldsGroupView('');
	$result->addClass('fields-group-widget-title');
	$result
		->addField($widget_title_text)
		->addField($widget_title_link)
		->addField($widget_title_font)
		->addField($widget_title_font_size)
		->addField($widget_title_font_color)
		->addField($widget_title_font_style)
		->addField($widget_title_background_color)
	;

	return $result;
}

function getFilteringFieldsGroupViews(CWidgetFormView $form, array $fields): CWidgetFieldsGroupView {
	$severities = new CWidgetFieldSeveritiesView($fields['severities']);
	$evaltype = new CWidgetFieldRadioButtonListView($fields['evaltype']);
	$tags = new CWidgetFieldTagsView($fields['tags']);
	$host_based_filtering = new CWidgetFieldCheckBoxView($fields['host_based_filtering']);
	$groupids = new CWidgetFieldMultiSelectGroupView($fields['groupids']);
	$groupids->getView(); // Initialize multiselect property of CWidgetFieldMultiSelectGroupView to be able to call getId()
	$exclude_groupids = new CWidgetFieldMultiSelectGroupView($fields['exclude_groupids']);
	$hostids = new CWidgetFieldMultiSelectHostView($fields['hostids']);
	$hostids->setFilterPreselect(['id' => $groupids->getId(), 'submit_as' => 'groupid']);
	$show_unacknowledged_only = new CWidgetFieldCheckBoxView($fields['show_unacknowledged_only']);
	$show_suppressed = new CWidgetFieldCheckBoxView($fields['show_suppressed']);
	$problem = new CWidgetFieldTextBoxView($fields['problem']);

	$result = new CWidgetFieldsGroupView(_('Filtering'));
	$result->addClass('fields-group-filtering');
	$result
		->addField($severities)
		->addItem((new CTag('hr')))
		->addField($evaltype)
		->addItem(new Ctag('span', true))
		->addField($tags)
		->addItem((new CTag('hr')))
		->addField($host_based_filtering)
		->addField($groupids)
		->addField($exclude_groupids)
		->addField($hostids)
		->addItem((new CTag('hr')))
		->addField($show_unacknowledged_only)
		->addField($show_suppressed)
		->addItem((new CTag('hr')))
		->addField($problem)
	;

	return $result;
}

function getTagListFieldsGroupViews(CWidgetFormView $form, array $fields): CWidgetFieldsGroupView {
	$tag_list = new CWidgetFieldTagsListView($fields['taglist']);

	$result = new CWidgetFieldsGroupView(_('Tag list'));
	$result->addClass('fields-group-tag-list');
	$result
		->addField($tag_list)
	;

	return $result;
}

function getSortingFieldsGroupViews(CWidgetFormView $form, array $fields): CWidgetFieldsGroupView {
	$first_level_sorting_column = new CWidgetFieldSortColumnView($fields['first_level_sorting_column']);
	$first_level_sorting_mode = new CWidgetFieldSortOrderView($fields['first_level_sorting_order']);
	$second_level_sorting_enabled = new CWidgetFieldCheckBoxView($fields['second_level_sorting_enabled']);
	$second_level_sorting_column = new CWidgetFieldSortColumnView($fields['second_level_sorting_column']);
	$second_level_sorting_mode = new CWidgetFieldSortOrderView($fields['second_level_sorting_order']);
	$third_level_sorting_enabled = new CWidgetFieldCheckBoxView($fields['third_level_sorting_enabled']);
	$third_level_sorting_column = new CWidgetFieldSortColumnView($fields['third_level_sorting_column']);
	$third_level_sorting_mode = new CWidgetFieldSortOrderView($fields['third_level_sorting_order']);

	$result = new CWidgetFieldsGroupView(_('Sorting'));
	$result->addClass('fields-group-sorting');
	$result
		->addField($first_level_sorting_column)
		->addField($first_level_sorting_mode)
		->addField($second_level_sorting_enabled)
		->addField($second_level_sorting_column)
		->addField($second_level_sorting_mode)
		->addField($third_level_sorting_enabled)
		->addField($third_level_sorting_column)
		->addField($third_level_sorting_mode)
	;

	return $result;
}

function getDisplayOptionsFieldsGroupViews(CWidgetFormView $form, array $fields): CWidgetFieldsGroupView {
	$rf_rate = new CWidgetFieldSelectView($fields['rf_rate']);
	$show_lines = new CWidgetFieldIntegerBoxView($fields['show_lines']);
	$show_columns_header = new CWidgetFieldCheckBoxView($fields['show_columns_header']);
	$display_mode = new CWidgetFieldDisplayStyleView($fields['display_mode']);
	$acknowledge_problem_style = new CWidgetFieldAcknowledgeProblemStyleView($fields['acknowledge_problem_style']);
	$font = new CWidgetFieldFontFamilyView($fields['font']);
	$font_size = new CWidgetFieldIntegerBoxView($fields['font_size']);
	$font_color = new CWidgetFieldColorView($fields['font_color']);
	$background_color = new CWidgetFieldColorView($fields['background_color']);
	$show_horizontal_scrollbar = new CWidgetFieldCheckBoxView($fields['show_horizontal_scrollbar']);
	$show_vertical_scrollbar = new CWidgetFieldCheckBoxView($fields['show_vertical_scrollbar']);

	$result = new CWidgetFieldsGroupView(_('Display options'));
	$result->addClass('fields-group-display-options');
	$result
		->addField($rf_rate)
		->addField($show_lines)
		->addField($show_columns_header)
		->addField($display_mode)
		->addField($acknowledge_problem_style)
		->addField($font)
		->addField($font_size)
		->addField($font_color)
		->addField($background_color)
		->addField($show_horizontal_scrollbar)
		->addField($show_vertical_scrollbar)
	;

	return $result;
}

function getColumnsGroupViews(CWidgetFormView $form, array $fields): CWidgetFieldsGroupView {
	$columns = new CWidgetFieldColumnsListView($fields['columnlist']);

	$result = new CWidgetFieldsGroupView(_('Columns'));
	$result->addClass('fields-group-columns');
	$result
		->addField($columns)
	;

	return $result;
}

function getSummaryRowFieldsGroupViews(CWidgetFormView $form, array $fields): CWidgetFieldsGroupView {
	$summary_row_name = new CWidgetFieldTextBoxView($fields['summary_row_name']);
	$summary_row_link = new CWidgetFieldTextBoxView($fields['summary_row_link']);
	$summary_row_severities_count = new CWidgetFieldSeveritiesCountView($fields['summary_row_show_severities_count']);
	$summary_row_severities_total_count = new CWidgetFieldSeveritiesCountView($fields['summary_row_show_severities_total_count']);

	$result = new CWidgetFieldsGroupView(_('Summary row'));
	$result->addClass('fields-group-summary-row');
	$result
		->addField($summary_row_name)
		->addField($summary_row_link)
		->addField($summary_row_severities_count)
		->addField($summary_row_severities_total_count)
	;

	return $result;
}
