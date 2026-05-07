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

enum FontFamilyType: int
{
    const __default = self::ARIAL;

    case GEORGIA = 0;
	case PALATINO_LINOTYPE = 1;
	case TIMES_NEW_ROMAN = 2;
    case ARIAL = 3;
    case ARIAL_BLACK = 4;
    case COMIC_SANS_MS = 5;
    case IMPACT = 6;
    case LUCIDA = 7;
    case TAHOMA = 8;
    case TREBUCHET = 9;
    case VERDANA = 10;
    case COURIER_NEW = 11;
    case LUCIDA_CONSOLE = 12;
    case RUBIK = 13;

    public function getName(): string
    {
        return match ($this) {
            self::GEORGIA => 'Georgia',
            self::PALATINO_LINOTYPE => 'Palatino',
            self::TIMES_NEW_ROMAN => 'Times New Roman',
            self::ARIAL => 'Arial',
            self::ARIAL_BLACK => 'Arial Black',
            self::COMIC_SANS_MS => 'Comic Sans',
            self::IMPACT => 'Impact',
            self::LUCIDA => 'Lucida Sans',
            self::TAHOMA => 'Tahoma',
            self::TREBUCHET => 'Helvetica',
            self::VERDANA => 'Verdana',
            self::COURIER_NEW => 'Courier New',
            self::LUCIDA_CONSOLE => 'Lucida Console',
            self::RUBIK => 'Rubik',
        };
    }

    public function getValue(): string
    {
        return match ($this) {
            self::GEORGIA => 'Georgia, serif',
            self::PALATINO_LINOTYPE => '"Palatino Linotype", "Book Antiqua", Palatino, serif',
            self::TIMES_NEW_ROMAN => '"Times New Roman", Times, serif',
            self::ARIAL => 'Arial, Helvetica, sans-serif',
            self::ARIAL_BLACK => '"Arial Black", Gadget, sans-serif',
            self::COMIC_SANS_MS => '"Comic Sans MS", cursive, sans-serif',
            self::IMPACT => 'Impact, Charcoal, sans-serif',
            self::LUCIDA => '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
            self::TAHOMA => 'Tahoma, Geneva, sans-serif',
            self::TREBUCHET => '"Trebuchet MS", Helvetica, sans-serif',
            self::VERDANA => 'Verdana, Geneva, sans-serif',
            self::COURIER_NEW => '"Courier New", Courier, monospace',
            self::LUCIDA_CONSOLE => '"Lucida Console", Monaco, monospace',
            self::RUBIK => 'Rubik, sans-serif',
        };
    }

    public function fromInt(int $value): FontFamilyType
    {
        return match ($value) {
            0 => self::GEORGIA,
            1 => self::PALATINO_LINOTYPE,
            2 => self::TIMES_NEW_ROMAN,
            3 => self::ARIAL,
            4 => self::ARIAL_BLACK,
            5 => self::COMIC_SANS_MS,
            6 => self::IMPACT,
            7 => self::LUCIDA,
            8 => self::TAHOMA,
            9 => self::TREBUCHET,
            10 => self::VERDANA,
            11 => self::COURIER_NEW,
            12 => self::LUCIDA_CONSOLE,
            13 => self::RUBIK,
        };
    }

    public static function toArray(): array {
        return array_reduce(static::cases(), fn ($result, $value) => $result + [$value->value => $value->getName()], []);
    }
}
