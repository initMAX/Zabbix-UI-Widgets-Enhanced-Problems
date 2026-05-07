<?php
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

use CCol;
use CColHeader;
use CRow;
use CTable;
use Modules\EnhancedProblems\Includes\Helpers\ProblemHelper;
use Modules\EnhancedProblems\Type\AcknowledgeProblemStyleType;
use Modules\EnhancedProblems\Type\DisplayStyleType;
use Modules\EnhancedProblems\Type\FontFamilyType;

class WidgetTable extends CTable
{
    public function __construct(
        private bool $showColumnsHeader,
        private DisplayStyleType $displayStyle,
        private AcknowledgeProblemStyleType $acknowledgeProblemStyle,
        private FontFamilyType $fontFamily,
        private int $fontSize,
        private string $fontColor,
        private array $columnList,
        private array $problems,
        private array $users,
    ) {
        parent::__construct();
        parent::addClass('enhanced-problems-widget-table');
        parent::setAttribute('data-acknowledge-problem-style', $this->acknowledgeProblemStyle->getValue());
        parent::addStyle(sprintf('font-family: %s;', $this->fontFamily->getValue()));
        parent::addStyle(sprintf('font-size: %spx;', $this->fontSize));
        $this->fontColor !== '' && parent::addStyle(sprintf('color: #%s;', $this->fontColor));

        if ($showColumnsHeader) {
            $thead = null;

            if ($displayStyle === DisplayStyleType::TABULAR) {
                foreach ($this->columnList as $column) {
                    if (!$column['enabled']) continue;

                    $col = new CColHeader($column['label'] ?: $column['name']);
                    $col->addStyle(sprintf('width: %s%%;', $column['width']));

                    $thead[] = $col;
                }
            } else if ($displayStyle === DisplayStyleType::LINES) {
                $col = new CColHeader();

                foreach ($this->columnList as $index => $column) {
                    if (!$column['enabled']) continue;

                    $col->addItem($column['label'] ?: $column['name']);

                    if (array_key_last($this->columnList) !== $index) {
                        $col->addItem(': ');
                    }
                }

                $thead[] = $col;
            }

            parent::setHeader($thead);
        }

        foreach ($this->problems as $problem) {
            $row = new CRow();
            $row->setAttribute('data-eventid', $problem['eventid']);
            $row->setAttribute('data-severity', $problem['severity']);
            $row->setAttribute('data-acknowledged', $problem['acknowledged'] ? 'true' : 'false');
            $row->setAttribute('data-url', $problem['url_tag'] ?? '');

            if ($displayStyle === DisplayStyleType::TABULAR) {
                foreach ($this->columnList as $column) {
                    if (!$column['enabled']) continue;

                    if ($column['name'] === 'First occurrence') {
                        $col = new CCol(zbx_date2str(DATE_TIME_FORMAT_SECONDS, $problem['clock']));
                        $col->addStyle(sprintf('width: %s%%;', $column['width']));
                    }
                    else if ($column['name'] === 'First occurrence tag') {
                        $col = new CCol($problem['first_occurrence_tag'] ?? '');
                        $col->addStyle(sprintf('width: %s%%;', $column['width']));
                    }
                    else if ($column['name'] === 'Last occurrence') {
                        $col = new CCol(zbx_date2str(DATE_TIME_FORMAT_SECONDS, $problem['clock_last']));
                        $col->addStyle(sprintf('width: %s%%;', $column['width']));
                    }
                    else if ($column['name'] === 'Host') {
                        $col = new CCol($problem['hosts_names'] ?? _('Unknown hosts names'));
                        $col->addStyle(sprintf('width: %s%%;', $column['width']));
                    }
                    else if ($column['name'] === 'Problem') {
                        $col = new CCol($problem['name']);
                        $col->addStyle(sprintf('width: %s%%;', $column['width']));
                    }
                    else if ($column['name'] === 'Action icons') {
                        $col = new CCol(ProblemHelper::getActions($problem, $this->users));
                        $col->addStyle(sprintf('width: %s%%;', $column['width']));
                    }
                    else if ($column['name'] === 'Count') {
                        $col = new CCol($problem['count'] ?? '');
                        $col->addStyle(sprintf('width: %s%%;', $column['width']));
                    }
                    else if ($column['tagindex'] != -1) {
                        $col = new CCol($problem['tagColumns'][$column['name']] ?? '');
                        $col->addStyle(sprintf('width: %s%%;', $column['width']));
                    }
                    else {
                        $col = new CCol($column['name']);
                        $col->addStyle(sprintf('width: %s%%;', $column['width']));
                    }

                    $row->addItem($col);
                }
            } else if ($displayStyle === DisplayStyleType::LINES) {
                $col = new CCol();

                foreach ($this->columnList as $column) {
                    if (!$column['enabled']) continue;

                    if ($column['name'] === 'Host') {
                        $col->addItem($problem['hosts_names'] ?? _('Unknown hosts names'));
                    }
                    else if ($column['name'] === 'Problem') {
                        $col->addItem($problem['name']);
                    }
                    else if ($column['name'] === 'Time') {
                        $col->addItem($problem['time']);
                    }
                    else if ($column['name'] === 'Ack') {
                        $col->addItem($problem['acknowledged'] ? 'yes' : 'no');
                    }
                    else if ($column['name'] === 'Actions') {
                        $col->addItem('actions');
                    }
                    else if ($column['tagindex'] != -1) {
                        $col->addItem($problem['tagColumns'][$column['name']] ?? '');
                    }
                }

                $row->addItem($col);
            }

            parent::addRow($row);
        }
    }

    public function toString($destroy = true): string {
        return parent::toString($destroy);
    }
}
