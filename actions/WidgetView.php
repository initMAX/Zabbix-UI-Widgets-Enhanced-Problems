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


namespace Modules\EnhancedProblems\Actions;

use API;
use CArrayHelper;
use CControllerDashboardWidgetView;
use CControllerResponseData;
use Modules\EnhancedProblems\Services\HostsLoader;
use Modules\EnhancedProblems\Type\AcknowledgeProblemStyleType;
use Modules\EnhancedProblems\Type\DisplayStyleType;
use Modules\EnhancedProblems\Type\FontFamilyType;
use Modules\EnhancedProblems\Type\FontStyleType;
use Modules\EnhancedProblems\Type\SeveritiesCountType;
use Modules\EnhancedProblems\Type\SortColumnType;
use Modules\EnhancedProblems\Type\SortOrderType;

/**
 * Enhanced problems widget view action.
 */

class WidgetView extends CControllerDashboardWidgetView
{
	protected function init(): void {
		parent::init();

		$this->addValidationRules([
			'initial_load' => 'in 0,1'
		]);
	}

	protected function doAction(): void {
		$this->normalizeFieldsValues();

		// Setup initial data
		$data = [
			'name' => $this->getInput('name', $this->widget->getName()),
			'fields_values' => $this->fields_values,
			'user' => [
				'debug_mode' => $this->getDebugMode(),
			],
		];

		// Get data
		$data += $this->getData();

		// Fill trigger data
		$this->fillTriggerData($data);

		// Fill host data
		$this->fillHostData($data);

		// Fill column data
		$this->fillColumnData($data);

		// Normalize suppression
		$this->normalizeSuppression($data);

		// Sort data
		$this->sortData($data);

		// Trim data
		$this->trimData($data);

		// Count severities if necessary
		if ($this->fields_values['summary_row_show_severities_count'] !== SeveritiesCountType::NONE) {
			$this->countSeverities($data);
		}

		// Count total severities if necessary
		if ($this->fields_values['summary_row_show_severities_total_count'] !== SeveritiesCountType::NONE) {
			$this->countTotalSeverities($data);
		}

		// Set response
		$this->setResponse(new CControllerResponseData($data));
	}

	protected function normalizeFieldsValues(): void {
		// Normalize Widget title font
		$this->fields_values['widget_title_font'] = FontFamilyType::from((int) $this->fields_values['widget_title_font']);

		// Normalize Widget font size
		$this->fields_values['widget_title_font_size'] = (int) $this->fields_values['widget_title_font_size'];

		// Normalize Widget font style
		foreach ($this->fields_values['widget_title_font_style'] as &$fontStyle) {
			$fontStyle = FontStyleType::from((int) $fontStyle);
		}

		// Normalize Summary row show severities count
		$this->fields_values['summary_row_show_severities_count'] = SeveritiesCountType::from((int) $this->fields_values['summary_row_show_severities_count']);

		// Normalize Summary row show severities total count
		$this->fields_values['summary_row_show_severities_total_count'] = SeveritiesCountType::from((int) $this->fields_values['summary_row_show_severities_total_count']);

		// Normalize Display style
		$this->fields_values['display_mode'] = DisplayStyleType::from((int) $this->fields_values['display_mode']);

		// Normalize Acknowledge problem style
		$this->fields_values['acknowledge_problem_style'] = AcknowledgeProblemStyleType::from((int) $this->fields_values['acknowledge_problem_style']);

		// Normalize Font family
		$this->fields_values['font'] = FontFamilyType::from((int) $this->fields_values['font']);

		// Normalize Font size
		$this->fields_values['font_size'] = (int) $this->fields_values['font_size'];

		// Normalize First level sorting field
		$this->fields_values['first_level_sorting_column'] = SortColumnType::from((int) $this->fields_values['first_level_sorting_column']);

		// Normalize First level sorting order
		$this->fields_values['first_level_sorting_order'] = SortOrderType::from((int) $this->fields_values['first_level_sorting_order']);

		// Normalize Second level sorting field
		$this->fields_values['second_level_sorting_column'] = SortColumnType::from((int) $this->fields_values['second_level_sorting_column']);

		// Normalize Second level sorting order
		$this->fields_values['second_level_sorting_order'] = SortOrderType::from((int) $this->fields_values['second_level_sorting_order']);

		// Normalize Third level sorting field
		$this->fields_values['third_level_sorting_column'] = SortColumnType::from((int) $this->fields_values['third_level_sorting_column']);

		// Normalize Third level sorting order
		$this->fields_values['third_level_sorting_order'] = SortOrderType::from((int) $this->fields_values['third_level_sorting_order']);
	}

	protected function getData(): array {
		$filter = [];
		$data = [];

		if (!empty($this->fields_values['severities'])) {
			$filter['severities'] = $this->fields_values['severities'];
		}

		if (!empty($this->fields_values['tags'])) {
			$filter['evaltype'] = $this->fields_values['evaltype'];
			$filter['tags'] = $this->fields_values['tags'];
		}

		if ($this->fields_values['host_based_filtering']) {
			$filter['hostids'] = HostsLoader::getHostids(
				$this->fields_values['groupids'] ?? null,
				$this->fields_values['exclude_groupids'] ?? null,
				$this->fields_values['hostids'] ?? null,
			);
		}

		if ($this->fields_values['show_unacknowledged_only']) {
			$filter['acknowledged'] = false;
		}

		if (!$this->fields_values['show_suppressed']) {
			$filter['suppressed'] = true;
		}

		// TODO: maybye do not trim the values before sort.
		if ($this->fields_values['show_lines']) {
			$filter['limit'] = $this->fields_values['show_lines'];
		}

		if (!empty($this->fields_values['problem'])) {
			$filter['search']['name'] = $this->fields_values['problem'];
		}

		$problems = API::Problem()->get([
				'output' => API_OUTPUT_EXTEND,
				'source' => EVENT_SOURCE_TRIGGERS,
				'object' => EVENT_OBJECT_TRIGGER,
				'recent' => false,
				'selectTags' => API_OUTPUT_EXTEND,
				'selectAcknowledges' => API_OUTPUT_EXTEND,
				'selectSuppressionData' => API_OUTPUT_EXTEND,
				'preservekeys' => true,
				'sortfield' => ['eventid'],		// TODO: remove, just for testing diff with problem screen
				'sortorder' => ZBX_SORT_DOWN,	// TODO: remove, just for testing diff with problem screen
			] + $filter
		);

		[
			'problems' => $data['problems'],
			'users' => $data['users']
		] = $this->getEventsProblemsIconsData($problems);

		return $data;
	}

	protected function getEventsProblemsIconsData(array $problems): array {
		$userids = [];

		$result = getEventsSuppressions($problems);
		$suppressions = $result['data'];
		$userids += $result['userids'];

		$result = getEventsMessages($problems);
		$messages = $result['data'];
		$userids += $result['userids'];

		$triggers = [];
		$triggerids = array_column($problems, 'objectid', 'objectid');
		if ($triggerids) {
			$triggers = API::Trigger()->get([
				'output' => ['triggerid', 'priority'],
				'triggerids' => $triggerids,
				'preservekeys' => true
			]);
		}

		$actions = getEventsAlertsOverview($problems);

		$result = getEventsSeverityChanges($problems, $triggers);
		$severities = $result['data'];
		$userids += $result['userids'];

		$users = [];
		if ($userids) {
			$users = API::User()->get([
				'output' => ['userid', 'name', 'surname', 'username'],
				'userids' => array_keys($userids),
				'preservekeys' => true
			]);
		}

		foreach ($problems as &$problem) {
			$id = $problem['eventid'];
			$problem += [
				'suppressions' => $suppressions[$id] ?? [],
				'severities' => $severities[$id] ?? [],
				'messages' => $messages[$id] ?? [],
				'triggers' => $triggers[$id] ?? [],
				'actions' => $actions[$id] ?? []
			];
		}
		unset($problem);

		return [
			'problems' => $problems,
			'users' => $users
		];
	}

	protected function fillTriggerData(array &$data): void {
		// Get objectids when source is EVENT_OBJECT_TRIGGER
		$triggerids = array_column(
			array_filter($data['problems'], fn($item) => $item['source'] == EVENT_SOURCE_TRIGGERS),
			'objectid',
		);

		// Deduplicate triggerids
		$triggerids = array_unique($triggerids);

		// Get triggers
		$data['triggers'] = API::Trigger()->get([
			'triggerids' => $triggerids,
			'preservekeys' => true,
			// 'active' => 1, // TODO: remove, just for testing diff with problem screen
			'monitored' => true,
			'skipDependent' => null,
			'output' => API_OUTPUT_EXTEND,
			'selectHosts' => API_OUTPUT_EXTEND,
		]);
	}

	protected function fillHostData(array &$data): void {
		foreach ($data['problems'] as &$problem) {
			$triggerid = $problem['objectid'];
			$trigger = $data['triggers'][$triggerid] ?? null;

			// If trigger is not found, skip
			if ($trigger === null) {
				$problem['hosts'] = null;
				$problem['hosts_names'] = _('Unknown hosts names');
				continue;
			}

			// If trigger is found, get all hosts
			$problem['hosts'] = $trigger['hosts'] ?? null;
			$problem['hosts_names'] = implode(', ', array_column($problem['hosts'], 'name'));
		}
	}

	protected function normalizeSuppression(array &$data): void {
		foreach ($data['problems'] as $problem_key => &$problem) {
			// Suppression - Set default value (not supressed)
			$data['problems'][$problem_key]['suppressed'] = (string) ZBX_PROBLEM_SUPPRESSED_FALSE;

			// foreach ($problem['acknowledges'] as $acknowledge) {
			// 	// Set suppressed flag
			// 	if ($acknowledge['action'] & ZBX_PROBLEM_UPDATE_SUPPRESS) {
			// 		$data['problems'][$problem_key]['suppressed'] = (string) ZBX_PROBLEM_SUPPRESSED_TRUE;
			// 	} else if ($acknowledge['action'] & ZBX_PROBLEM_UPDATE_UNSUPPRESS) {
			// 		$data['problems'][$problem_key]['suppressed'] = (string) ZBX_PROBLEM_SUPPRESSED_FALSE;
			// 	}
			// }

			// if (count($problem['suppression_data']) > 0) {
			// 	$data['problems'][$problem_key]['suppressed'] = (string) ZBX_PROBLEM_SUPPRESSED_TRUE;
			// }

			$recentylSuppressed = isEventRecentlySuppressed($problem['acknowledges'], $suppression_action);
			$recentylUnsuppressed = isEventRecentlyUnsuppressed($problem['acknowledges'], $unsuppression_action);

			if ($recentylSuppressed) {
				$data['problems'][$problem_key]['suppressed'] = (string) ZBX_PROBLEM_SUPPRESSED_TRUE;
			} else if ($recentylUnsuppressed) {
				$data['problems'][$problem_key]['suppressed'] = (string) ZBX_PROBLEM_SUPPRESSED_FALSE;
			}
		}
	}

	protected function sortData(array &$data): void {
		$criteria[] = [
			'field' => $this->fields_values['first_level_sorting_column']->dataColumnName(),
			'order' => $this->fields_values['first_level_sorting_order']->direction(),
		];

		if ($this->fields_values['second_level_sorting_enabled']) {
			$criteria[] = [
				'field' => $this->fields_values['second_level_sorting_column']->dataColumnName(),
				'order' => $this->fields_values['second_level_sorting_order']->direction(),
			];
		}

		if ($this->fields_values['third_level_sorting_enabled']) {
			$criteria[] = [
				'field' => $this->fields_values['third_level_sorting_column']->dataColumnName(),
				'order' => $this->fields_values['third_level_sorting_order']->direction(),
			];
		}

		// Divide acknowledged_suppressed to separated acknowledged and suppressed criteria
		$newCriteria = [];

		foreach ($criteria as $criterium) {
			if ($criterium['field'] === SortColumnType::ACKNOWLEDGED_AND_TIME_ACKNOWLEDGED->dataColumnName()) {
				// Nahradíme jedním původním dvě nové
				$newCriteria[] = [
					'field' => 'acknowledged',
					'order' => $criterium['order'],
				];
				$newCriteria[] = [
					'field' => 'suppressed',
					'order' => $criterium['order'],
				];
			} else {
				// Zachováme beze změny
				$newCriteria[] = $criterium;
			}
		}

		$criteria = $newCriteria;

		CArrayHelper::sort($data['problems'], $criteria);
	}

	protected function trimData(array &$data): void {
		$data['problems'] = array_slice($data['problems'], 0, $this->fields_values['show_lines']);
	}

	protected function fillColumnData(array &$data): void {
		foreach ($data['problems'] as $problem_key => &$problem) {
			// Last occurrence
			// Default value: same as clock
			$problem['clock_last'] = $problem['clock'];

			// Search in acknowledges for Last occurrence and Count
			foreach ($problem['acknowledges'] as $acknowledge) {
				// When found a closed problem - remove it and continue on this and parent loop
				if ($acknowledge['action'] == ZBX_PROBLEM_UPDATE_CLOSE) {
					unset($data['problems'][$problem_key]);
					continue 2;
				}

				$message = $acknowledge['message'];
				$pattern = '/^tally:\s*(.+)$/';

				if (preg_match($pattern, $message)) {
					preg_match($pattern, $message, $matches);

					$problem['count'] = $matches[1];
					$problem['clock_last'] = $acknowledge['clock'];
				}
			}

			// Remove supressed problems if needed
			// Probably not needed, because Zabbix API returns data without suppressed problems if is not set show_suppressed
			//if ($fields['show_suppressed'] == 0 && $data['problems'][$problem_key]['supressed'] == (string) ZBX_PROBLEM_SUPPRESSED_TRUE) {
			//	unset($data['problems'][$problem_key]);
			//	continue;
			//}

			// Process tags and search for Count (tally), FirstOccurrence
			foreach ($problem['tags'] as $tag['id'] => $tag) {
				// Tag columns
				$problem['tagColumns'][$tag['tag']] = $tag['value'];

				// Tally
				if ($tag['tag'] == 'Tally') {
					// Save only if count is not set by acknowledges
					if (!array_key_exists('count', $data['problems'][$problem_key])) {
						$data['problems'][$problem_key]['tally_tag'] = $tag['value'];
					}
				}
				// FirstOccurrence
				else if ($tag['tag'] == 'FirstOccurrence') {
					$data['problems'][$problem_key]['first_occurrence_tag'] = $tag['value'];
				}
				// URL
				else if ($tag['tag'] == 'URL') {
					$data['problems'][$problem_key]['url_tag'] = $tag['value'];
				}
			}
		}
	}

	protected function countSeverities(array &$data): void {
		$data['severities_count'] = [
			ZBX_SEVERITY_OK => 0,
			TRIGGER_SEVERITY_NOT_CLASSIFIED => 0,
			TRIGGER_SEVERITY_INFORMATION => 0,
			TRIGGER_SEVERITY_WARNING => 0,
			TRIGGER_SEVERITY_AVERAGE => 0,
			TRIGGER_SEVERITY_HIGH => 0,
			TRIGGER_SEVERITY_DISASTER => 0,
		];

		foreach ($data['problems'] as $problem) {
			if (
				$this->fields_values['summary_row_show_severities_count'] === SeveritiesCountType::NON_ACK_ONLY
				&& $problem['acknowledged'] === EVENT_NOT_ACKNOWLEDGED
			) {
				$data['severities_count'][$problem['severity']]++;
			}
			else if (
				$this->fields_values['summary_row_show_severities_count'] === SeveritiesCountType::ACK_ONLY
				&& $problem['acknowledged'] === EVENT_ACKNOWLEDGED
			) {
				$data['severities_count'][$problem['severity']]++;
			}
			else if ($this->fields_values['summary_row_show_severities_count'] === SeveritiesCountType::BOTH) {
				$data['severities_count'][$problem['severity']]++;
			}
		}
	}

	protected function countTotalSeverities(array &$data): void {
		$data['severities_total_count'] = 0;

		foreach ($data['problems'] as $problem) {
			if (
				$this->fields_values['summary_row_show_severities_total_count'] === SeveritiesCountType::NON_ACK_ONLY
				&& $problem['acknowledged'] === EVENT_NOT_ACKNOWLEDGED
			) {
				$data['severities_total_count']++;
			}
			else if (
				$this->fields_values['summary_row_show_severities_total_count'] === SeveritiesCountType::ACK_ONLY
				&& $problem['acknowledged'] === EVENT_ACKNOWLEDGED
			) {
				$data['severities_total_count']++;
			}
			else if ($this->fields_values['summary_row_show_severities_total_count'] === SeveritiesCountType::BOTH) {
				$data['severities_total_count']++;
			}
		}
	}
}
