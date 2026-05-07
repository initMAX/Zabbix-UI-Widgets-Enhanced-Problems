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

use CButton;
use CCol;
use CRow;
use CTable;
use CTemplateTag;
use CTextBox;
use CWidgetFieldView;

class CWidgetFieldTagsListView extends CWidgetFieldView
{
	public function __construct(CWidgetFieldTagsList $field) {
		$this->field = $field;
	}

	public function getView(): CTable {
		$tags = $this->field->getValue();

		if (!$tags) {
			$tags = [CWidgetFieldTagsList::DEFAULT_TAG];
		}

		$tags_table = (new CTable())
			->setId('tags_table_'.$this->field->getName())
			->addClass('table-tags')
			->addClass(ZBX_STYLE_TABLE_INITIAL_WIDTH);

		$i = 0;

		foreach ($tags as $tag) {
			$tags_table->addItem($this->getRowTemplate($tag, $i));

			$i++;
		}

		$tags_table->addRow(
			(new CCol(
				(new CButton('tags_add', _('Add')))
					->addClass(ZBX_STYLE_BTN_LINK)
					->addClass('element-table-add')
					->setEnabled(!$this->isDisabled())
			))->setColSpan(3)
		);

		return $tags_table;
	}

	public function getJavaScript(): string {
		return '
			jQuery("#tags_table_'.$this->field->getName().'")
				.dynamicRows({template: "#'.$this->field->getName().'-row-tmpl"});
		';
	}

	public function getTemplates(): array {
		return [
			new CTemplateTag($this->field->getName().'-row-tmpl', $this->getRowTemplate(CWidgetFieldTagsList::DEFAULT_TAG))
		];
	}

	private function getRowTemplate(array $tag, $row_num = '#{rowNum}'): CRow {
		return (new CRow([
			(new CTextBox($this->field->getName().'['.$row_num.'][tag]', $tag['tag']))
				->setWidth(ZBX_TEXTAREA_FILTER_SMALL_WIDTH)
				->setAriaRequired($this->isRequired())
				->setEnabled(!$this->isDisabled() || $row_num === '#{rowNum}')
				->setAttribute('placeholder', _('tag')),
			(new CCol(
				(new CButton($this->field->getName().'['.$row_num.'][remove]', _('Remove')))
					->addClass(ZBX_STYLE_BTN_LINK)
					->addClass('element-table-remove')
					->setEnabled(!$this->isDisabled() || $row_num === '#{rowNum}')
			))->addClass(ZBX_STYLE_NOWRAP)
		]))->addClass('form_row');
	}
}
