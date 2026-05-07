<?php

/**
 * @var CView $this
 */

$form = (new CForm())
	->cleanItems()
	->setId('acknowledge_form')
	->addVar('action', 'popup.acknowledge.create')
    ->addVar(CSRF_TOKEN_NAME, CCsrfTokenHelper::get('acknowledge'))
	->addVar('eventids', $data['eventids'])
    ->addVar('suppress_problem', ZBX_PROBLEM_UPDATE_SUPPRESS)
    ->addVar('suppress_time_option', ZBX_PROBLEM_SUPPRESS_TIME_DEFINITE)
;

$form_list = new CFormGrid();

$form_list->addItem([
    (new CLabel(_('Problem'))),
    (new CDiv('TODO'))
        ->addClass(ZBX_STYLE_WORDBREAK)
]);

$form_list->addItem([
    (new CLabel(_('Time acknowledge'))),
    (new CDiv([
        (new CDateSelector('suppress_until_problem', 'now+10m'))
            ->setDateFormat(ZBX_FULL_DATE_TIME)
            ->setPlaceholder(_('Insert acknowledge period or date'))
            ->setAriaRequired()
            ->setEnabled(true),
        (new CSpan([_('WARNING: Date is longer than 1 month from today!')]))
            ->setId('long_suppress_warning_message')
            ->addClass('long_suppress_warning_message')
            ->setAttribute('style', 'display: none;'),
    ]))->addClass(ZBX_STYLE_HOR_LIST)
]);

$form_list->addItem([
    (new CLabel(_('Message'), 'message')),
    (new CTextArea('message'))
        ->setWidth(ZBX_TEXTAREA_BIG_WIDTH)
        ->setAttribute('maxlength', DB::getFieldLength('acknowledges', 'message'))
]);

$form->addItem($form_list);

ob_start();
include __DIR__.'/js/timeacknowledge.view.js.php';

$form->addItem(new CScriptTag(ob_get_clean()));


$output = [
	'header' => _('Time acknowledge'),
	'body' => (new CDiv([$data['errors'], $form]))->toString(),
	'buttons' => [
		[
			'title' => _('Save'),
			'class' => '',
			'keepOpen' => true,
			'isSubmit' => true,
			'action' => 'time_acknowledge_popup.submitAcknowledge(overlay);'
		]
	],
	'script_inline' => 'time_acknowledge_popup.init();',
];

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
	CProfiler::getInstance()->stop();
	$output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output);
