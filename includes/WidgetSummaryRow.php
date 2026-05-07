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

use CLink;
use CSeverityHelper;
use CSpan;
use CTag;

class WidgetSummaryRow extends CTag
{
    /**
     * Create widget summary row.
     * 
     * @param string $name Name of the summary row
     * @param string $link Link of the summary row
     * @param array $severitiesCount Severities count
     * @param int $severitiesTotalCount Total severities count
     */
    public function __construct(
        private string $name,
        private string $link,
        private array $severitiesCount,
        private int $severitiesTotalCount,
    ) {
        parent::__construct('div', true);
        parent::addClass('enhanced-problems-widget-summary-row');

        // Add title
        $title = new CSpan();
        if ($link) {
            $title->addItem((new CLink($name, $link))->setTarget('blank'));
        } else {
            $title->addItem($name);
        }
        parent::addItem($title);

        // Add severities count
        $container = new CSpan();
        $container->addClass('severities-count');

        if ($severitiesCount[TRIGGER_SEVERITY_NOT_CLASSIFIED] > 0) {
            $badge = new CSpan($severitiesCount[TRIGGER_SEVERITY_NOT_CLASSIFIED]);
            $badge->addClass('severities-count__item');
            $badge->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_NOT_CLASSIFIED));

            $container->addItem($badge);
        }

        if ($severitiesCount[TRIGGER_SEVERITY_INFORMATION] > 0) {
            $badge = new CSpan($severitiesCount[TRIGGER_SEVERITY_INFORMATION]);
            $badge->addClass('severities-count__item');
            $badge->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_INFORMATION));

            $container->addItem($badge);
        }

        if ($severitiesCount[TRIGGER_SEVERITY_WARNING] > 0) {
            $badge = new CSpan($severitiesCount[TRIGGER_SEVERITY_WARNING]);
            $badge->addClass('severities-count__item');
            $badge->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_WARNING));

            $container->addItem($badge);
        }

        if ($severitiesCount[TRIGGER_SEVERITY_AVERAGE] > 0) {
            $badge = new CSpan($severitiesCount[TRIGGER_SEVERITY_AVERAGE]);
            $badge->addClass('severities-count__item');
            $badge->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_AVERAGE));

            $container->addItem($badge);
        }

        if ($severitiesCount[TRIGGER_SEVERITY_HIGH] > 0) {
            $badge = new CSpan($severitiesCount[TRIGGER_SEVERITY_HIGH]);
            $badge->addClass('severities-count__item');
            $badge->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_HIGH));

            $container->addItem($badge);
        }

        if ($severitiesCount[TRIGGER_SEVERITY_DISASTER] > 0) {
            $badge = new CSpan($severitiesCount[TRIGGER_SEVERITY_DISASTER]);
            $badge->addClass('severities-count__item');
            $badge->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_DISASTER));

            $container->addItem($badge);
        }

        parent::addItem($container);

        // Add total severities count
        $container = new CSpan();
        $container->addClass('severities-count');

        $badge = new CSpan(sprintf('∑ %s', $severitiesTotalCount));
        $badge->addClass('severities-count__item');

        $container->addItem($badge);

        parent::addItem($container);
    }

    public function toString($destroy = true): string {
        return parent::toString($destroy);
    }
}
