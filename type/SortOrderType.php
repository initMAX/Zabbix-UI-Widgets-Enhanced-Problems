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

enum SortOrderType: int
{
    const __default = self::ASC;

    case ASC = 0;
    case DESC = 1;

    public function label(): string
    {
        return match ($this) {
            self::ASC => _('ASC'),
            self::DESC => _('DESC'),
        };
    }

    public function direction(): string
    {
        return match ($this) {
            self::ASC => ZBX_SORT_UP,
            self::DESC => ZBX_SORT_DOWN,
        };
    }
}
