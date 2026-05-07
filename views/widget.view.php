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


use Modules\EnhancedProblems\Includes\WidgetSummaryRow;
use Modules\EnhancedProblems\Includes\WidgetTable;
use Modules\EnhancedProblems\Includes\WidgetTitle;


/**
 * Enhanced problems widget view.
 *
 * @var CView $this
 * @var array $data
 */

// Widget title
$widgetTitle = null;
if ($this->data['fields_values']['show_widget_title'] ?? false) {
	$widgetTitle = new WidgetTitle(
		$this->data['fields_values']['widget_title_text'],
		$this->data['fields_values']['widget_title_link'],
		$this->data['fields_values']['widget_title_font'],
		$this->data['fields_values']['widget_title_font_size'],
		$this->data['fields_values']['widget_title_font_style'],
		$this->data['fields_values']['widget_title_font_color'],
		$this->data['fields_values']['widget_title_background_color'],
	);
}

// Summary row
$summaryRow = null;
if ($this->data['fields_values']['show_summary_row'] ?? false) {
	$summaryRow = new WidgetSummaryRow(
		$this->data['fields_values']['summary_row_name'],
		$this->data['fields_values']['summary_row_link'],
		$this->data['severities_count'],
		$this->data['severities_total_count'],
	);
}

// Table
$body = null;
if ($this->data['problems']) {
	$body = new WidgetTable(
		$this->data['fields_values']['show_columns_header'],
		$this->data['fields_values']['display_mode'],
		$this->data['fields_values']['acknowledge_problem_style'],
		$this->data['fields_values']['font'],
		$this->data['fields_values']['font_size'],
		$this->data['fields_values']['font_color'],
		$this->data['fields_values']['columnlist'],
		$this->data['problems'],
		$this->data['users'],
	);
}
else {
	$body = new CDiv('No data found');
	$body->addClass('no-data-message zi-search-large');
}

// Output
$output['body'] = $widgetTitle?->toString() ?? null;
$output['body'] .= $summaryRow?->toString() ?? null;
$output['body'] .= $body->toString();
$output['body'] .= (new CTag('style', true, sprintf('.dashboard-widget-enhancedproblems { background: #%s; }', $this->data['fields_values']['background_color'])))->toString();

$output['action_token'] = [
	'popup.acknowledge.create' => CCsrfTokenHelper::get('acknowledge')
];

if ($messages = get_and_clear_messages()) {
	$output['messages'] = array_column($messages, 'message');
}

if (array_key_exists('user', $this->data) && $this->data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
	CProfiler::getInstance()->stop();
	$output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output, JSON_THROW_ON_ERROR);
