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


class CWidgetEnhancedProblems extends CWidget {
    #tableClass = '.enhanced-problems-widget-table';

    #keydown = {shift: false, ctrl: false};
    #mouse = {lmb: false}

    #selectedids = [];
    #urlTagValue = '';

    #action_token = {};

    hasPadding() {
        return false;
    }

    onActivate() {
        this.#registerTableRowDoubleClick();
        this.#registerContextMenuOpen();
        this.#registerSelection();
    }

    // MULTISELECT
    setContents(response) {
        const focused = this._body.querySelector('tr[data-eventid].focused')?.dataset.eventid;

        super.setContents(response);

        this.#setSelectedRows(this.#selectedids);

        this.#action_token = response.action_token;

        this._body.querySelector(`tr[data-eventid="${focused||0}"]`)?.classList.add('focused');
    }

    isUserInteracting() {
        return super.isUserInteracting() || this.#keydown.shift || this.#keydown.ctrl || this.#mouse.lmb;
    }

    #mousedownEventHandler(e) {
        const row = e.target.matches('tr[data-eventid]') ? e.target : e.target.closest('tr[data-eventid]');

        if (row === null) {
            return;
        }

        if (e.button !== 0) {
            if (!row.matches('.selected')) {
                this.#clearSelectedRows();
                this.#selectedids = [row.dataset.eventid];
                row.classList.add('selected');
            }

            return;
        }

        this.#mouse.lmb = true;

        if (this.#keydown.ctrl) {
            row.classList.add('pre-selected');
        }
        else {
            [...this._body.querySelectorAll('tr[data-eventid].selected')].map(tr => tr.classList.remove('selected'));
        }

        const focused = this._body.querySelector('tr[data-eventid].focused')??row;

        if (this.#keydown.shift) {
            const trs = [...row.parentNode.querySelectorAll('tr[data-eventid]')];
            let start, end;
            [start, end] = [trs.indexOf(focused), trs.indexOf(row)].sort((a, b) => a - b);
            trs.slice(start, end + 1).map(tr => tr.classList.add('pre-selected'));
        }

        if (!this.#keydown.crtl && !this.#keydown.shift) {
            row.classList.add('pre-selected');
        }

        if (!this.#keydown.shift || !focused.classList.contains('focused')) {
            focused.classList.remove('focused');
            row.classList.add('focused');
        }
    }

    #mouseupEventHandler(e) {
        if (e.button !== 0) {
            return;
        }

        const pre_seleced = this._body.querySelectorAll('tr[data-eventid].pre-selected');

        if (pre_seleced.length == 1) {
            pre_seleced[0].classList.toggle('selected', !pre_seleced[0].classList.contains('selected'));
            pre_seleced[0].classList.remove('pre-selected');
        }
        else {
            for (const tr of pre_seleced) {
                tr.classList.add('selected');
                tr.classList.remove('pre-selected');
            }
        }

        this.#mouse.lmb = false;
        this.#selectedids = [...this._body.querySelectorAll('tr[data-eventid].selected')].map(tr => tr.dataset.eventid);
        this.#urlTagValue = this._body.querySelector('tr[data-eventid].selected')?.dataset.url;
    }

    #mousemoveEventHandler(e) {
        const row = e.target.matches('tr[data-eventid]') ? e.target : e.target.closest('tr[data-eventid]');

        if (row === null) {
            return;
        }

        const trs = [...row.parentNode.querySelectorAll('tr[data-eventid]')];
        const focused = this._body.querySelector('tr[data-eventid].focused');
        let start, end;
        [start, end] = [trs.indexOf(focused), trs.indexOf(row)].sort((a, b) => a - b);
        trs.map((tr, i) => tr.classList.toggle('pre-selected', start <= i && i <= end));
    }

    #registerSelection() {
        document.addEventListener('mouseup', e => this.#clearSelectionOnMouseUp(e), {passive: true});
        document.addEventListener('keydown', e => (this.#keydown = {shift: e.shiftKey, ctrl: e.ctrlKey||e.metaKey}), {passive: true});
        document.addEventListener('keyup', e => (this.#keydown = {shift: e.shiftKey, ctrl: e.ctrlKey||e.metaKey}), {passive: true});
        this._container.addEventListener('mouseup', e => this.#mouseupEventHandler(e), {passive: true});
        this._container.addEventListener('mousedown', e => this.#mousedownEventHandler(e), {passive: true});
        this._container.addEventListener('mousemove', e => this.#mouse.lmb && this.#mousemoveEventHandler(e), {passive: true});

        return;
    }

    #clearSelectionOnMouseUp(e) {
        const dialog_click = e.target.closest('[role="dialog"]') !== null;
        const context_menu_click = e.target.closest('[role="menu"]') !== null;
        const within_widget_click = this._container.contains(e.target);

        if (!dialog_click && !context_menu_click && !within_widget_click) {
            this.#clearSelectedRows();
        }
    }

    #setSelectedRows(eventids) {
        for (const row of this._body.querySelectorAll('tbody tr[data-eventid]')) {
            if (eventids.includes(row.getAttribute('data-eventid'))) {
                row.classList.add('selected');
            }
        }
    }

    #clearSelectedRows() {
        this.#selectedids = [];

        for (const row of this._body.querySelectorAll('tbody tr[data-eventid]')) {
            row.classList.remove('selected');
        }
    }

    // EVENT DOUBLECLICK
    #registerTableRowDoubleClick() {
        this._body.addEventListener('dblclick', e => {
            if (!e.target.closest(this.#tableClass + ' tbody')) {
                return;
            }

            const row = e.target.closest('tr');

            if (row) {
                const eventids = [row.getAttribute('data-eventid')];

                // PopUp('widget.enhancedproblems.acknowledge.popup', {
                //     'eventid': eventid,
                // });
                this.#openPopupAndRefreshOnSubmit('widget.enhancedproblems.acknowledge.popup', { eventids });
            }
        });
    }

    // CONTEXT MENU
    #registerContextMenuOpen() {
        this._body.addEventListener('contextmenu', e => {
            if (!e.target.closest(this.#tableClass + ' tbody')) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            jQuery(e.target).menuPopup(this.#getContextMenu(), e, {
                position: {
                    of: e,
                    my: 'left top',
                    collision: 'fit',
                },
                background_layer: false,
            });

            const menu = jQuery(e.target).data('menu_popup');

            menu && menu.on('contextmenu', e => e.preventDefault());
        });
    }

    #getContextMenu() {
        const showAcknowledge = this._fields.show_context_menu_acknowledge;
        const showUnacknowledge = this._fields.show_context_menu_unacknowledge;
        const showTimeAcknowledge = this._fields.show_context_menu_time_acknowledge;
        const showClose = this._fields.show_context_menu_close;
        const showJournal = this._fields.show_context_menu_journal;
        const showCreateTts = this._fields.show_context_menu_create_tts;
        const showAddToTts = this._fields.show_context_menu_add_to_tts;
        const showBoxInfo = this._fields.show_context_menu_box_info;
        const showFollowUrl = this._fields.show_context_menu_follow_url;
        const showCopyToClipboard = this._fields.show_context_menu_copy_to_clipboard;

        const acknowledgeItem = {
            label: t('Acknowledge'),
            disabled: this.#selectedids.length === 0,
            clickCallback: () => {
                this.acknowledgeAction({
                        eventids: this.#selectedids
                    }
                );
                this.#clearSelectedRows();
            }
        };

        const timeAcknowledgeItem = {
            label: t('Time acknowledge'),
            disabled: this.#selectedids.length === 0,
            clickCallback: () => {
                this.timeAcknowledgePopup({
                        eventids: this.#selectedids,
                    }
                );
            }
        };

        const unacknowledgeItem = {
            label: t('Unacknowledge'),
            disabled: this.#selectedids.length === 0,
            clickCallback: () => {
                this.unacknowledgeAction({
                        eventids: this.#selectedids,
                    }
                );
                this.#clearSelectedRows();
            }
        };

        const closeItem = {
            label: t('Delete'),
            disabled: this.#selectedids.length === 0,
            clickCallback: (this.#selectedids.length > 1)?
                () => {
                    this.closeWithConfirmAction({
                            eventids: this.#selectedids,
                        }
                    );
                }
            :
                () => {
                    this.closeAction({
                            eventids: this.#selectedids,
                        }
                    );
                }
        };

        const journalItem = {
            label: t('Journal'),
            disabled: this.#selectedids.length === 0,
            clickCallback: () => {
                this.journalPopUp({
                    eventids: this.#selectedids,
                });
            }
        };

        const createTtsItem = {
            label: t('Create TTS'),
            disabled: this.#selectedids.length === 0,
            clickCallback: () => {
                this.createIssuePopUp({
                    eventids: this.#selectedids,
                    dashboardid: this._dashboard.dashboardid,
                    widgetid: this._widgetid,
                });
            }
        };

        const addToTtsItem = {
            label: t('Add to TTS'),
            disabled: this.#selectedids.length === 0,
            clickCallback: (e) => {
                this.addIssuePopUp({
                    eventids: this.#selectedids,
                    dashboardid: this._dashboard.dashboardid,
                    widgetid: this._widgetid,
                });
            }
        };

        const boxInfoItem = {
            label: t('Box info'),
            disabled: this.#selectedids.length === 0 || this.#selectedids.length > 1,
            clickCallback: () => {
                this.boxInfoPopUp({
                    eventid: this.#selectedids[0],
                });
            }
        };

        const followUrlItem = {
            label: t('Follow URL'),
            disabled: this.#selectedids.length === 0 || this.#selectedids.length > 1 || this.#urlTagValue === '',
            clickCallback: (e) => {
                this.followUrlAction({
                    url_tag: this.#urlTagValue,
                });
            }
        };

        const copyToClipboardItem = {
            label: t('Copy to clipboard'),
            disabled: this.#selectedids.length === 0,
            clickCallback: () => {
                this.copyToClipboardAction({
                    eventids: this.#selectedids,
                });
            }
        };

        // Compose menu
        const actionsSection = {
            label: t('Actions'),
            items: [],
        };

        const itemsToCheck = [
            { condition: showAcknowledge, item: acknowledgeItem },
            { condition: showTimeAcknowledge, item: timeAcknowledgeItem },
            { condition: showUnacknowledge, item: unacknowledgeItem },
            { condition: showClose, item: closeItem },
            { condition: showJournal, item: journalItem },
            { condition: showCreateTts, item: createTtsItem },
            { condition: showAddToTts, item: addToTtsItem },
            { condition: showBoxInfo, item: boxInfoItem },
            { condition: showFollowUrl, item: followUrlItem },
            { condition: showCopyToClipboard, item: copyToClipboardItem }
        ];

        actionsSection.items.push(
            ...itemsToCheck.filter(({ condition }) => condition !== 0).map(({ item }) => item)
        );

        return [actionsSection];
    }

    acknowledgeAction(parameters) {
		const url = new Curl('zabbix.php', false);
		url.setArgument('action', 'popup.acknowledge.create');
        url.setArgument('output', 'ajax');

        const formData = new FormData();
        formData.append('acknowledge_problem', 2);
        formData.append(CSRF_TOKEN_NAME, this.#action_token['popup.acknowledge.create']);

        for (const eventid of parameters.eventids) {
            formData.append('eventids[]', eventid);
        }

		sendAjaxData(url.getUrl(), {
            data: formData,
            processData: false,
            contentType: false,
			dataType: 'json',
			method: 'POST'
		}).always(
            e => this._update()
        );
	}

	timeAcknowledgePopup(parameters) {
		// this._overlay = PopUp('widget.enhancedproblems.timeacknowledge.popup', parameters);
        // this._overlay.$dialogue[0].addEventListener('dialogue.submit', e => {
        //     this._update();
        // });
        this.#openPopupAndRefreshOnSubmit('widget.enhancedproblems.timeacknowledge.popup', parameters);
	}

	unacknowledgeAction(parameters) {
		const url = new Curl('zabbix.php', false);
		url.setArgument('action', 'popup.acknowledge.create');
        url.setArgument('output', 'ajax');

        const formData = new FormData();
        formData.append('unacknowledge_problem', 16);
        formData.append('unsuppress_problem', 64);
        formData.append(CSRF_TOKEN_NAME, this.#action_token['popup.acknowledge.create']);

        for (const eventid of parameters.eventids) {
            formData.append('eventids[]', eventid);
        }

		sendAjaxData(url.getUrl(), {
			data: formData,
            processData: false,
            contentType: false,
			dataType: 'json',
			method: 'POST',
		}).always(
            e => this._update()
        );
	}

	closeAction(parameters) {
		const url = new Curl('zabbix.php', false);
		url.setArgument('action', 'popup.acknowledge.create');
		url.setArgument('output', 'ajax');

		const formData = new FormData();
		formData.append('close_problem', 1);
        formData.append('scope', 0);
        formData.append(CSRF_TOKEN_NAME, this.#action_token['popup.acknowledge.create']);

        for (const eventid of parameters.eventids) {
            formData.append('eventids[]', eventid);
        }

        sendAjaxData(url.getUrl(), {
			data: formData,
            processData: false,
            contentType: false,
			dataType: 'json',
			method: 'POST'
		}).always(
            e => {
                // TODO: Solve long refresh delay after closing
                this._update()
                // $.publish('widget.refresh', { widgetid: this._widgetid })
            }
        );
	}

	closeWithConfirmAction(parameters) {
		const content = document.createElement('div');
		content.textContent = `Are you sure to close ${parameters.eventids.length} problems?`;
		content.classList.add('enhanced-problem-confirm-body');

		overlayDialogue({
			'title': 'Confirmation needed',
			'class': 'modal-popup',
			'content': content,
			'buttons': [
				{
					title: t('Ok'),
					action: () => {
						this.closeAction(parameters);
					}
				},
				{
					title: t('Cancel'),
					class: 'btn-alt',
					action: () => {}
				}
			]
		});
	}

    journalPopUp(parameters) {
		// PopUp('widget.acknowledge.popup', parameters);
        this.#openPopupAndRefreshOnSubmit('widget.acknowledge.popup', parameters);
	}

    createIssuePopUp(parameters) {
		// PopUp('widget.enhancedproblems.issue.create', parameters);
        this.#openPopupAndRefreshOnSubmit('widget.enhancedproblems.issue.create', parameters);
	}

    addIssuePopUp(parameters) {
		// this._overlay = PopUp('widget.enhancedproblems.issue.add', parameters);
        // TODO: Fix javascript console error
		// this._overlay.$dialogue[0].addEventListener('overlay.close', e => $.publish('widget.refresh', { widgetid: this._widgetid }));
        this.#openPopupAndRefreshOnSubmit('widget.enhancedproblems.issue.add', parameters);
	}

    boxInfoPopUp(parameters) {
		PopUp('widget.enhancedproblems.boxinfo', parameters);
	}

    followUrlAction(parameters) {
		window.open(parameters.url_tag, '_blank');
	}

	copyToClipboardAction() {
        // Table creation
		const table = document.createElement('table');

        // Table header
		const tableHeader = this._target.querySelector('table' + this.#tableClass + ' thead');
		table.appendChild(tableHeader.cloneNode(true));

        // Table body
		const tableBody = document.createElement('tbody');
		table.appendChild(tableBody);

        // Get selected rows
        const selectedRows = this._target.querySelectorAll('table' + this.#tableClass + ' tbody tr.selected');

        // Add selected rows to the table
        selectedRows.forEach(row => {
            const newRow = row.cloneNode(true);
            tableBody.appendChild(newRow);
        });

		// Remove hint box elements
		const hintBox = table.getElementsByClassName('hint-box');
		Array.from(hintBox).forEach(element => {
			element.remove();
		});
		const actionIcons = table.querySelectorAll('[data-hintbox="1"]');
		Array.from(actionIcons).forEach(element => {
			element.remove();
		});

		// Rename Action column
		const actionColumn = table.querySelector('[data-column-name="event-actions-icons"]');
		if (actionColumn) {
            actionColumn.innerText = 'Link';
        }

		// Add severities styling
		const events = table.querySelectorAll('tbody tr');
		events.forEach(event => {
			const severityColor = '#' + event.dataset['severityColor'];
			const acknowledged = event.dataset['acknowledged'];

			if (acknowledged == 'true') {
				event.style.backgroundColor = severityColor;
			}
			else {
				event.style.color = severityColor;
			}
		});

		// Add borders to table
		table.style.borderCollapse = 'collapse';
		const borderElements = table.querySelectorAll('td, th');
		Array.from(borderElements).forEach(element => {
			element.style.border = '1px solid black';
		});

		// Clean all unnecessary attributes
		const allElements = table.getElementsByTagName('*');
		Array.from(allElements).forEach(element => {
			element.removeAttribute('class');

			element.getAttributeNames().forEach(attribute => {
				if (attribute.startsWith('data-')) {
					element.removeAttribute(attribute);
				}
				if (attribute.startsWith('aria-')) {
					element.removeAttribute(attribute);
				}
			});
		});

		// Copy to clipboard
		copyTableToClipboard(table);
	}

    #openPopupAndRefreshOnSubmit(action, parameters) {
        this._overlay = PopUp(action, parameters);
        this._overlay.$dialogue[0].addEventListener('dialogue.submit', e => {
            this._update();
        });
    }
}

async function copyTableToClipboard(table) {
	try {
		// Create a new ClipboardItem with the table's HTML content
		const clipboardItem = new ClipboardItem({
			"text/html": new Blob(
				[table.outerHTML],
				{
					type: "text/html",
				},
			),
			"text/plain": new Blob(
				[table.innerText],
				{
					type: "text/plain",
				},
			),
		});

		// Write the ClipboardItem to the clipboard
		await navigator.clipboard.write([clipboardItem]);
	} catch (err) {
		console.error('Unable to copy to clipboard.', err);
	}
}
