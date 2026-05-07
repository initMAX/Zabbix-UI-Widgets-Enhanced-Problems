<?php
/*
** Copyright (C) 2001-2024 initMAX s.r.o.
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


namespace Modules\EnhancedProblems\Services;

use API;


class HostsLoader {
    /**
     * Get hostids based on groupids, exclude_groupids and hostids
     * 
     * @param array|null $groupids
     * @param array|null $exclude_groupids
     * @param array|null $hostids
     */
    public static function getHostids(?array $groupids, ?array $exclude_groupids, ?array $hostids): array
    {
        // Get all groupids in subgroups
        $groupids = $groupids ? getSubGroups($groupids) : null;
        $exclude_groupids = $exclude_groupids ? getSubGroups($exclude_groupids) : null;

        // Output definition
        $output = [
            'hostid',
        ];

        /*
            * Matrix of possible combinations:
            *
            *		G	EG	H
            *	A | 0	0	0	-> Get all hosts from Zabbix
            *	B | 1 	0	0	-> Get all hosts from groups
            *	C | 0	1	0	-> Get all hosts from Zabbix except hosts from excluded groups
            *	D | 1   1   0   -> Get all hosts from groups except hosts from excluded groups
            *	E | 0	0	1	-> Get hosts by hostids
            *	F | 1   0   1   -> Get all hosts from groups and hosts by hostids
            *	G | 0   1   1   -> Get all hosts from Zabbix except hosts from excluded groups and hosts by hostids
            *	H | 1   1   1   -> Get all hosts from groups except hosts from excluded groups and hosts by hostids
            */

        // Case A: Get all hosts from Zabbix
        if (empty($groupids) && empty($exclude_groupids) && empty($hostids)) {
            // Get all hosts
            return array_keys(API::Host()->get([
                'output' => $output,
                'preservekeys' => true,
            ]));
        }
        // Case B: Get all hosts from groups
        else if (!empty($groupids) && empty($exclude_groupids) && empty($hostids)) {
            // Get all hosts from groups
            return array_keys(API::Host()->get([
                'output' => $output,
                'groupids' => $groupids,
                'preservekeys' => true,
            ]));
        }
        // Case C: Get all hosts from Zabbix except hosts from excluded groups
        else if (empty($groupids) && !empty($exclude_groupids) && empty($hostids)) {
            // Get all hosts
            $all_hosts = API::Host()->get([
                'output' => $output,
                'preservekeys' => true,
            ]);

            // Get excluded hosts
            $excluded_groupids_hosts = API::Host()->get([
                'output' => $output,
                'groupids' => $exclude_groupids,
                'preservekeys' => true,
            ]);

            return array_keys(array_diff_key($all_hosts, $excluded_groupids_hosts));
        }
        // Case D: Get all hosts from groups except hosts from excluded groups
        else if (!empty($groupids) && !empty($exclude_groupids) && empty($hostids)) {
            // Get all hosts from groups
            $all_groupids_hosts = API::Host()->get([
                'output' => $output,
                'groupids' => $groupids,
                'preservekeys' => true,
            ]);

            // Get excluded hosts
            $excluded_groupids_hosts = API::Host()->get([
                'output' => $output,
                'groupids' => $exclude_groupids,
                'preservekeys' => true,
            ]);

            return array_keys(array_diff_key($all_groupids_hosts, $excluded_groupids_hosts));
        }
        // Case E: Get hosts by hostids
        else if (empty($groupids) && empty($exclude_groupids) && !empty($hostids)) {
            return $hostids;
        }
        // Case F: Get all hosts from groups and hosts by hostids
        else if (!empty($groupids) && empty($exclude_groupids) && !empty($hostids)) {
            // Get all hosts from groups
            $all_groupids_hosts = array_keys(API::Host()->get([
                'output' => $output,
                'groupids' => $groupids,
                'preservekeys' => true,
            ]));

            return array_merge($all_groupids_hosts, $hostids);
        }
        // Case G: Get all hosts from Zabbix except hosts from excluded groups and hosts by hostids
        else if (empty($groupids) && !empty($exclude_groupids) && !empty($hostids)) {
            // Get all hosts
            $all_hosts = API::Host()->get([
                'output' => $output,
                'preservekeys' => true,
            ]);

            // Get excluded hosts
            $excluded_hosts = API::Host()->get([
                'output' => $output,
                'groupids' => $exclude_groupids,
                'preservekeys' => true,
            ]);

            $results = array_diff_key($all_hosts, $excluded_hosts);

            return array_keys(array_merge($results, $hostids));
        }
        // Case H: Get all hosts from groups except hosts from excluded groups and hosts by hostids
        else if (!empty($groupids) && !empty($exclude_groupids) && !empty($hostids)) {
            // Get all hosts from groups
            $all_groupids_hostids = array_keys(API::Host()->get([
                'output' => $output,
                'groupids' => $groupids,
                'preservekeys' => true,
            ]));

            // Get excluded hosts
            $excluded_hostids = array_keys(API::Host()->get([
                'output' => $output,
                'groupids' => $exclude_groupids,
                'preservekeys' => true,
            ]));

            $results = array_diff_key($all_groupids_hostids, $excluded_hostids);

            return array_merge($results, $hostids);
        }

        // This should never happen
        return [];
    }
}
