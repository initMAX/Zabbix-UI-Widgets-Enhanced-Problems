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

class CWidgetFieldColumnsList extends CWidgetField
{
	public function __construct(string $name, string $label = null)
	{
		parent::__construct($name, $label);

		$this
			->setSaveType(ZBX_WIDGET_FIELD_TYPE_STR)
			->setValidationRules(['type' => API_OBJECTS, 'fields' => [
				'enabled'				=> ['type' => API_INT32, 'default' => 1],
				'name'					=> ['type' => API_STRING_UTF8, 'default' => '', 'length' => 255],
				'width'					=> ['type' => API_INT32, 'default' => 0],
				'label'					=> ['type' => API_STRING_UTF8, 'default' => '', 'length' => 255],
				'tagindex'				=> ['type' => API_INT32, 'default' => -1],
			]]);
	}

	public function getDefault(): array
	{
		return [
			0 => [
				'enabled' => 0,
				'name' => _('First occurrence'),
				'width' => 0,
				'label' => '',
				'tagindex' => -1,
			],
			1 => [
				'enabled' => 1,
				'name' => _('Last occurrence'),
				'width' => 15,
				'label' => '',
				'tagindex' => -1,
			],
			2 => [
				'enabled' => 1,
				'name' => _('Host'),
				'width' => 25,
				'label' => '',
				'tagindex' => -1,
			],
			3 => [
				'enabled' => 1,
				'name' => _('Problem'),
				'width' => 50,
				'label' => '',
				'tagindex' => -1,
			],
			4 => [
				'enabled' => 1,
				'name' => _('Action icons'),
				'width' => 10,
				'label' => '',
				'tagindex' => -1,
			],
			5 => [
				'enabled' => 0,
				'name' => _('Count'),
				'width' => 0,
				'label' => '',
				'tagindex' => -1,
			],
			6 => [
				'enabled' => 0,
				'name' => _('First occurrence tag'),
				'width' => 0,
				'label' => '',
				'tagindex' => -1,
			],
		];
	}

	public function setValue($value): self
	{
		$this->value = (array) $value;

		if (empty($this->value)) {
			$this->value = $this->getDefault();
		}

		return $this;
	}

	public function toApi(array &$widget_fields = []): void
	{
		$fields = [
			'enabled' => ZBX_WIDGET_FIELD_TYPE_INT32,
			'name' => ZBX_WIDGET_FIELD_TYPE_STR,
			'width' => ZBX_WIDGET_FIELD_TYPE_INT32,
			'label' => ZBX_WIDGET_FIELD_TYPE_STR,
			'tagindex' => ZBX_WIDGET_FIELD_TYPE_INT32,
		];

		foreach ($this->getValue() as $column_index => $val) {
			foreach (array_intersect_key($fields, $val) as $field => $field_type) {
				$widget_fields[] = [
					'type' => $field_type,
					'name' => implode('.', [$this->name, $column_index, $field]),
					'value' => $val[$field],
				];
			}
		}
	}
}
