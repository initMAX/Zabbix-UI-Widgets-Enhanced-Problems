<?php declare(strict_types = 0);
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


use Modules\EnhancedProblems\Widget;

/**
 * @var CView $this
 * @var array $data
 */

$form = (new CForm())
    ->cleanItems()
    ->setId('acknowledge_form')
    ->addItem((new CVar(CSRF_TOKEN_NAME, CCsrfTokenHelper::get('acknowledge')))->removeId())
    ->addVar('eventids', $data['eventids']);

$form_list = new CFormList();

$form_list->addRow(
    new CLabel(_('Problem')),
    (new CDiv($data['problem_name']))->addClass(ZBX_STYLE_WORDBREAK)
);

if (array_key_exists('tag_alarm_number_value', $data))
{
    $form_list->addRow(
        new CLabel(_('AlarmNumber')),
        (new CDiv($data['tag_alarm_number_value']))->addClass(ZBX_STYLE_WORDBREAK)
    );
}

if (array_key_exists('tag_suppl_info_value', $data))
{
    $form_list->addRow(
        new CLabel(_('SupplInfo')),
        (new CDiv($data['tag_suppl_info_value']))->addClass(ZBX_STYLE_WORDBREAK)
    );
}

$form_list->addRow(
    new CLabel(_('Message'), 'message'),
    (new CTextArea('message', $data['message']??''))
        ->setWidth(ZBX_TEXTAREA_BIG_WIDTH)
        ->setAttribute('maxlength', DB::getFieldLength('acknowledges', 'message'))
        ->setEnabled($data['allowed_add_comments'])
);

if (array_key_exists('history', $data))
{
     $form_list->addRow(_('History'),
         (new CDiv(makeEventHistoryTable($data['history'], $data['users'])))
             ->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
             ->setAttribute('style', 'min-width: '.ZBX_TEXTAREA_BIG_WIDTH.'px;')
     );
}

$selected_events = count($data['eventids']);

if ($data['has_unack_events'])
{
    $form_list->addRow(_('Acknowledge'),
        (new CCheckBox('acknowledge_problem', ZBX_PROBLEM_UPDATE_ACKNOWLEDGE))
            ->onChange("$('#unacknowledge_problem').prop('disabled', this.checked)")
            ->setEnabled($data['allowed_acknowledge'])
    );
}

if ($data['has_ack_events'])
{
    $form_list->addRow(_('Unacknowledge'),
        (new CCheckBox('unacknowledge_problem', ZBX_PROBLEM_UPDATE_UNACKNOWLEDGE))
            ->onChange("$('#acknowledge_problem').prop('disabled', this.checked)")
            ->setEnabled($data['allowed_acknowledge'])
    );
}

$form->addItem($form_list);

$inline_js = <<<'JAVASCRIPT'
/**
 * @param {Overlay} overlay
 */
function submitAcknowledge(overlay) {
    var $form = overlay.$dialogue.find('form'),
        url = new Curl('zabbix.php'),
        form_data;

    $form.trimValues(['#message']);
    form_data = jQuery('#message, input:visible, input[type=hidden]', $form).serialize();
    url.setArgument('action', 'popup.acknowledge.create');

    overlay.xhr = sendAjaxData(url.getUrl(), {
        data: form_data,
        dataType: 'json',
        method: 'POST',
        beforeSend: function() {
            overlay.setLoading();
        },
        complete: function() {
            overlay.unsetLoading();
        }
    })
    .done(function(response) {
        overlay.$dialogue.find('.msg-bad').remove();

        if ('error' in response) {
            const message_box = makeMessageBox('bad', response.error.messages,
                response.error.title
            );

            message_box.insertBefore($form);
        }
        else {
            overlay.$dialogue[0].dispatchEvent(new CustomEvent('dialogue.submit'));
            overlayDialogueDestroy(overlay.dialogueid);
        }
    });
}
JAVASCRIPT;

$output = [
    'header' => $data['title'],
    'body' => (new CDiv([$data['errors'], $form]))->toString(),
    'buttons' => [
        [
            'title' => _('Save'),
            'class' => '',
            'keepOpen' => true,
            'isSubmit' => true,
            'action' => 'return submitAcknowledge(overlay);'
        ]
    ],
    'script_inline' => $inline_js,
];

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
    CProfiler::getInstance()->stop();
    $output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output);
