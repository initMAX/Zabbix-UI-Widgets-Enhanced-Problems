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


namespace Modules\EnhancedProblems\Type;

enum FontStyleType: int
{
	case BOLD = 0;
    case ITALIC = 1;
	case UNDERLINE = 2;

    public function getName(): string
    {
        return match ($this) {
            self::BOLD => _('Bold'),
            self::ITALIC => _('Italic'),
            self::UNDERLINE => _('Underline'),
        };
    }

    public function getValue(): string
    {
        return match ($this) {
            self::BOLD => 'bold',
            self::ITALIC => 'italic',
            self::UNDERLINE => 'underline',
        };
    }

    public static function toArray(): array {
        return array_reduce(static::cases(), fn ($result, $value) => $result + [$value->value => $value->getName()], []);
    }

    public function isBold(): bool {
        return $this === self::BOLD;
    }

    public function isItalic(): bool {
        return $this === self::ITALIC;
    }

    public function isUnderline(): bool {
        return $this === self::UNDERLINE;
    }
}
