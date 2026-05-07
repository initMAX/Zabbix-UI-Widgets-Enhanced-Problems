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

enum AcknowledgeProblemStyleType: int
{
    const __default = self::COLOR;

    case MONOCHROMATIC = 0;
    case COLOR = 1;

    public function label(): string
    {
        return match ($this) {
            self::MONOCHROMATIC => _('Monochromatic'),
            self::COLOR => _('Color'),
        };
    }

    public function getValue(): string
    {
        return match ($this) {
            self::MONOCHROMATIC => 'monochromatic',
            self::COLOR => 'color',
        };
    }
}
