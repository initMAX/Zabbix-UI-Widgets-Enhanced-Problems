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


namespace Modules\EnhancedProblems\Includes\Helpers;

use CArrayHelper;
use CButton;
use CButtonIcon;
use CDiv;
use CLink;
use CSimpleButton;
use CTableInfo;
use CIcon;

class ProblemHelper
{
    public static function getActions(array $problem, array $users): array
    {
        $result = [];

        if ($problem['acknowledged']) {
            $result[] = (new CIcon(ZBX_ICON_CHECK, _('Acknowledged')))->addClass(ZBX_STYLE_COLOR_POSITIVE);
        }

        if ($problem['suppressions']) {
            $result[] = makeEventSuppressionsProblemIcon($problem['suppressions'], $users);
        }

        if ($problem['messages']) {
            $result[] = makeEventMessagesIcon($problem['messages'], $users);
        }

        if ($problem['severities']) {
            $result[] = makeEventSeverityChangesIcon($problem['severities'], $users);
        }

        if ($problem['actions']) {
            $result[] = makeEventActionsIcon($problem['actions'], $problem['eventid']);
        }

        // TimeAcknowledge - full icon with hintbox
        $timeacknowledge_icon = ($problem['suppression_data'])? static::makeSuppressedProblemIcon($problem['suppression_data'], false) : null;
        // TimeAcknowledge - only icon without hintbox
        $timeacknowledge_icon_simple = ($problem['suppressed'])? (new CLink('⏳'))->setAttribute('style', 'border-bottom: none; margin: 0 0.5rem;') : null;

        // TODO: implement severities and actions icons
        //$severities_icon = makeEventSeverityChangesIcon($data['data']['actions']['severities'][$problem['eventid']], $data['data']['users']);
        //$actions_icon = makeEventActionsIcon($data['data']['actions']['actions'][$problem['eventid']], $problem['eventid']);

        // Custom icons
        // $issue_icon = static::makeIssueIcon($data['data']['actions']['messages'][$problem['eventid']], $data['data']['users']);
        // $journal_icon = static::makeJournalIcon($data['data']['actions']['messages'][$problem['eventid']], $data['data']['users']);
        $tag_url_icon = static::makeTagUrlIcon($problem['tag_url'] ?? null);

        // TimeAcknowledge - when are both icons available, show full icon with hintbox
        if ($timeacknowledge_icon !== null && $timeacknowledge_icon_simple !== null) {
            $result[] = $timeacknowledge_icon;
        }
        else if ($timeacknowledge_icon_simple !== null) {
            $result[] = $timeacknowledge_icon_simple;
        }

        // $journal_icon     !== null && $result[] = $journal_icon;
        // $severities_icon  !== null && $result[] = $severities_icon;
        // $actions_icon     !== null && $result[] = $actions_icon;
        // $issue_icon       !== null && $result[] = $issue_icon;
        $tag_url_icon     !== null && $result[] = $tag_url_icon;

        return $result;
    }

    /**
     * Renders an icon for suppressed problem.
     *
     * @param array<int, array{
     *     suppress_until: string,
     *     maintenance_name: string,
     *     username: string
     * }> $icon_data  Array of suppressed problem data.
     *
     * @throws Exception
     */
    public static function makeSuppressedProblemIcon(array $icon_data, bool $blink = false): CSimpleButton
    {
        $suppress_until_values = array_column($icon_data, 'suppress_until');

        if (in_array(ZBX_PROBLEM_SUPPRESS_TIME_INDEFINITE, $suppress_until_values)) {
            $suppressed_till = _s('Indefinitely');
        }
        else {
            $max_value = max($suppress_until_values);
            $suppressed_till = $max_value < strtotime('tomorrow')
                ? zbx_date2str(TIME_FORMAT, $max_value)
                : zbx_date2str(DATE_TIME_FORMAT, $max_value);
        }

        CArrayHelper::sort($icon_data, ['maintenance_name']);

        $maintenance_names = [];
        $username = '';

        foreach ($icon_data as $suppression) {
            if (array_key_exists('maintenance_name', $suppression)) {
                $maintenance_names[] = $suppression['maintenance_name'];
            }
            elseif (array_key_exists('username', $suppression)) {
                $username = $suppression['username'];
            }
        }

        $maintenances = implode(', ', $maintenance_names);

        return (new CButtonIcon(ZBX_ICON_EYE_OFF))
            ->addClass(ZBX_STYLE_COLOR_ICON)
            ->addClass($blink ? 'js-blink' : null)
            ->setHint(
                _s('Suppressed till: %1$s', $suppressed_till).
                ($username !== '' ? "\n"._s('Manually by: %1$s', $username) : '').
                ($maintenances !== '' ? "\n"._s('Maintenance: %1$s', $maintenances) : '')
            );
    }

    /**
     * Create icon with hintbox for event messages.
     *
     * @param array  $data
     * @param array  $data['messages']               Array of messages.
     * @param string $data['messages'][]['message']  Message text.
     * @param string $data['messages'][]['clock']    Message creation time.
     * @param array  $users                          User name, surname and username.
     *
     * @return CButton|null
     */
    public static function makeJournalIcon(array $data, array $users): ?CButton
    {
        $table = (new CTableInfo())->setHeader([_('Time'), _('User')]);
        $total = 0;

        foreach($data['messages'] as $message)
        {
            // Skip tally messages
            if(preg_match('/^tally:\s*(.+)$/', $message['message'])) continue;

            // Added in order to reuse makeActionTableUser().
            $message['action_type'] = ZBX_EVENT_HISTORY_MANUAL_UPDATE;

            // Limit
            if($table->getNumRows() <= ZBX_WIDGET_ROWS)
            {
                $table->addRow([
                    zbx_date2str(DATE_TIME_FORMAT_SECONDS, $message['clock']),
                    makeActionTableUser($message, $users),
                    zbx_nl2br($message['message'])
                ]);
            }

            $total++;
        }

        // Info about limit of displayed rows
        $limitInfo = ($total > ZBX_WIDGET_ROWS)?
            (new CDiv(
                (new CDiv(
                    (new CDiv(_s('Displaying %1$s of %2$s found', ZBX_WIDGET_ROWS, $total)))
                        ->addClass(ZBX_STYLE_TABLE_STATS)
                ))->addClass(ZBX_STYLE_PAGING_BTN_CONTAINER)
            ))->addClass(ZBX_STYLE_TABLE_PAGING)
            :
            null;

        return $total
            ? makeActionIcon([
                'icon' => ZBX_STYLE_ACTION_ICON_MSGS,
                'button' => true,
                'hint' => [
                    $table,
                    $limitInfo
                ],
                'num' => $total,
                'aria-label' => _xn('%1$s message', '%1$s messages', $total, 'screen reader', $total)
            ])
            : null
        ;
    }

    /**
     * Create icon for event tag_url
     *
     * @param string|null $url
     * @return CLink|null
     */
    public static function makeTagUrlIcon(?string $url): ?CLink
    {
        return $url ? (new CLink('👁‍🗨', $url))->setAttribute('target', '_blank') : null;
    }
}
