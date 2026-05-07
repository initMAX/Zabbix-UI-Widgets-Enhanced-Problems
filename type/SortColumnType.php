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


namespace Modules\EnhancedProblems\Type;

enum SortColumnType: int
{
    const __default = self::FIRST_OCCURRENCE;

    case FIRST_OCCURRENCE = 0;
    case LAST_OCCURRENCE = 1;
    case HOST = 2;
    case PROBLEM = 3;
    case SEVERITY = 4;
    case ACKNOWLEDGED_AND_TIME_ACKNOWLEDGED = 5;
    case FIRST_OCCURRENCE_TAG = 6;

    public function dataColumnName(): string
    {
        return match ($this) {
            self::FIRST_OCCURRENCE => 'clock',
            self::LAST_OCCURRENCE => 'clock_last',
            self::HOST =>  'hosts_names',
            self::PROBLEM =>  'name',
            self::SEVERITY =>  'severity',
            self::ACKNOWLEDGED_AND_TIME_ACKNOWLEDGED =>  'acknowledged_suppressed',
            self::FIRST_OCCURRENCE_TAG => 'first_occurrence_tag',
        };
    }

    public function toString()
    {
        return match ($this) {
            self::FIRST_OCCURRENCE => _('First occurrence'),
            self::LAST_OCCURRENCE => _('Last occurrence'),
            self::HOST => _('Hosts'),
            self::PROBLEM => _('Problem'),
            self::SEVERITY => _('Severity'),
            self::ACKNOWLEDGED_AND_TIME_ACKNOWLEDGED => _('Acknowledged & Time acknowledged'),
            self::FIRST_OCCURRENCE_TAG => _('First occurrence tag'),
        };
    }
}
