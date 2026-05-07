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


namespace Modules\EnhancedProblems\Actions;

use API;
use CController as CAction;
use CControllerResponseData;
use CControllerResponseFatal;

class TimeAcknowledge extends CAction
{
    public function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput() {
        $rules = [
            'eventids' => 'required|array',
        ];

        $ret = $this->validateInput($rules);

        if (!$ret) {
            $this->setResponse(new CControllerResponseFatal());
        }

        return $ret;
    }

    protected function checkPermissions() {
        // Permissions
        //$permit_user_types = [USER_TYPE_ZABBIX_ADMIN, USER_TYPE_SUPER_ADMIN];
        //return in_array($this->getUserType(), $permit_user_types);

        // This action does not require special permissions
        return true;
    }

    public function doAction() {
        // Get problems
        $problems = API::Problem()->get([
            'output' => ['eventid', 'name'],
            'eventids' => $this->getInput('eventids'),
            'preservekeys' => true,
        ]);

        $data = [
            'eventids' => $this->getInput('eventids'),
            'problems' => $problems,
        ];

        $response = new CControllerResponseData($data + [
            'errors' => hasErrorMessages() ? getMessages() : null,
            'user' => [
                'debug_mode' => $this->getDebugMode()
            ],
        ]);

        $this->setResponse($response);
    }
}
