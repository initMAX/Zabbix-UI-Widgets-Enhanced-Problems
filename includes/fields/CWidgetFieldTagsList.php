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


namespace Modules\EnhancedProblems\Includes\Fields;

use Zabbix\Widgets\CWidgetField;

class CWidgetFieldTagsList extends CWidgetField
{
	public const DEFAULT_VALUE = [];
	public const DEFAULT_TAG = [
		'tag' => '',
	];

	public function __construct(string $name, string $label = null) {
		parent::__construct($name, $label);

		$this
			->setDefault(self::DEFAULT_VALUE)
			->setSaveType(ZBX_WIDGET_FIELD_TYPE_STR)
			->setValidationRules([
				'type' => API_OBJECTS,
				'fields' => [
					'tag' => [
						'type' => API_STRING_UTF8,
						'flags' => API_REQUIRED,
						'length' => 255,
					],
				],
			])
		;
	}

	/**
	 * Get field value. If no value is set, will return default value.
	 */
	public function getValue() {
		$value = parent::getValue();

		foreach ($value as $index => $val) {
			if ($val['tag'] === '') {
				unset($value[$index]);
			}
		}

		return $value;
	}

	public function setValue($value): self {
		$this->value = (array) $value;

		return $this;
	}

	public function toApi(array &$widget_fields = []): void {
		$value = $this->getValue();

		foreach ($value as $index => $val) {
			$widget_fields[] = [
				'type' => ZBX_WIDGET_FIELD_TYPE_STR,
				'name' => implode('.', [$this->name, $index, 'tag']),
				'value' => $val['tag']
			];
		}
	}
}
