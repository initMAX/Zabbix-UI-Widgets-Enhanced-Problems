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

use CCheckBox;
use CCol;
use CColHeader;
use CDiv;
use CSpan;
use CNumericBox;
use CRow;
use CTable;
use CTag;
use CVar;
use CWidgetFieldView;
use CTemplateTag;
use CTextBox;

class CWidgetFieldColumnsListView extends CWidgetFieldView
{
	public function __construct(CWidgetFieldColumnsList $field)
	{
		$this->field = $field;
	}

	public function getView(): CTag
	{
		$columns = $this->field->getValue();

		$header = [
			'',
			(new CColHeader(_('Enabled')))->addStyle('width: 10%'),
			(new CColHeader(_('Name')))->addStyle('width: 30%'),
			(new CColHeader(_('Width')))->addStyle('width: 20%'),
			(new CColHeader(_('Label')))->addStyle('width: 40%'),
		];

		$table = (new CTable())
			->setId('list_'.$this->field->getName())
			->setHeader($header);

		foreach ($columns as $column_index => $column) {
			$column['prefix'] = [$column['tagindex'] >= 0 ? _('Show tag: ') : ''];
			$table->addRow($this->getRowTemplate($column_index, $column));
		}

		$table->setFooter(new CRow([
			(new CColHeader(_('Total: ')))->setColSpan(3)->addStyle('text-align: right'),
			(new CColHeader(''))->setColSpan(2)->addClass('js-columns-total')
		]));

		return $table;
	}

	public function getTemplates(): array {
		return [
			new CTemplateTag('columns-template', $this->getRowTemplate('#{rowNum}', [
				'enabled' => false,
				'prefix' => '#{prefix}',
				'name' => '#{name}',
				'width' => '#{width}',
				'label' => '#{label}' ,
				'tagindex' => '#{tagindex}',
			]))
		];
	}

	protected function getRowTemplate(string $index, array $value) {
		$field_name = $this->field->getName();
		$format = $field_name.'['.$index.'][%1$s]';
		$name = static function ($n) use ($format) { return sprintf($format, $n); };

		return (new CRow([
				(new CCol(
					(new CDiv([
						new CVar('sortorder['.$field_name.'][]', $index),
						new CVar($name('tagindex'), $value['tagindex'] ?? -1)
					]))->addClass(ZBX_STYLE_DRAG_ICON)
				))->addClass(ZBX_STYLE_TD_DRAG_ICON),
				[new Cvar($name('enabled'), 0), (new CCheckBox($name('enabled'), 1))->setChecked((bool) $value['enabled'])],
				new CDiv([
					$value['prefix'],
					(new CSpan($value['name']))->addClass('js-columns-name'),
					new CVar($name('name'), $value['name'])
				]),
				[new CNumericBox($name('width'), $value['width'], 10, false, false), '%'],
				new CTextBox($name('label'), $value['label'])
			]))->addClass('sortable');
	}
}
