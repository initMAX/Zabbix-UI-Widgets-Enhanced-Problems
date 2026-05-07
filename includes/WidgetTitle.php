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


namespace Modules\EnhancedProblems\Includes;

use CLink;
use CTag;
use Modules\EnhancedProblems\Type\FontFamilyType;
use Modules\EnhancedProblems\Type\FontStyleType;
use RuntimeException;

class WidgetTitle extends CTag
{
    /**
     * Create widget title.
     * 
     * @param string $text 
     * @param string $link 
     * @param FontFamilyType $font 
     * @param int $fontSize 
     * @param FontStyleType[] $fontStyle 
     * @param string $fontColor 
     * @param string $backgroundColor 
     * @return void 
     * @throws RuntimeException 
     */
    public function __construct(
        private string $text,
        private string $link,
        private FontFamilyType $font,
        private int $fontSize,
        private array $fontStyle,
        private string $fontColor,
        private string $backgroundColor,
    ) {
        parent::__construct('div', true);
        parent::addClass('enhanced-problems-widget-title');
        
        // Set font family
        parent::addStyle(sprintf('font-family: %s;', $font->getValue()));

        // Set font size
        parent::addStyle(sprintf('font-size: %spx;', $fontSize));

        // Set font style
        foreach ($fontStyle as $style) {
            if ($style->isItalic()) {
                parent::addStyle(sprintf('font-style: %s;', $style->getValue()));
            }
            if ($style->isBold()) {
                parent::addStyle(sprintf('font-weight: %s;', $style->getValue()));
            }
            if ($style->isUnderline()) {
                parent::addStyle(sprintf('text-decoration: %s;', $style->getValue()));
            }
        }

        // Set font color
        parent::addStyle(sprintf('color: #%s;', $fontColor));

        // Set background color
        parent::addStyle(sprintf('background-color: #%s;', $backgroundColor));

        // Add text with link or without
        if ($link) {
            parent::addItem((new CLink($text, $link))->setTarget('blank'));
        } else {
            parent::addItem($text);
        }
    }

    public function toString($destroy = true): string {
        return parent::toString($destroy);
    }
}
