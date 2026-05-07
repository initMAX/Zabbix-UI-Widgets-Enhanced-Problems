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


/**
 * @var CView $this
 */

?>//<script>

window.time_acknowledge_popup = new class {
	init() {
		$('#suppress_until_problem').change(() => this.showLongSuppressWarningMessage())
		$('#suppress_until_problem').on('input', () => this.showLongSuppressWarningMessage())
	}

	showLongSuppressWarningMessage() {
		const suppressUntilValue = document.getElementById('suppress_until_problem');
		const longSuppressWarningMessage = document.getElementById('long_suppress_warning_message');

		const givenDate = new Date(suppressUntil.value);
		let displayWarning = false;

		// Calculate the difference in milliseconds between the given date and the current date
		const timeDifference = givenDate - new Date();

		// Calculate the difference in months
		const differenceInMonths = timeDifference / (1000 * 60 * 60 * 24 * 30);

		// Check if the difference in months is greater than 1
		if (differenceInMonths > 1) {
			displayWarning = true;
		}

		const relativeDate = this.parseRelativeDate(suppressUntilValue);

		if (relativeDate) {
			// Create a new Date object representing one month from the current date
			const oneMonthFromNow = new Date();
			oneMonthFromNow.setMonth(oneMonthFromNow.getMonth() + 1);

			if (relativeDate > oneMonthFromNow) {
				displayWarning = true;
			}	
		}

		longSuppressWarningMessage.style.display = displayWarning ? 'block' : 'none';
	}

	parseRelativeDate(relativeDate) {
		if (relativeDate.startsWith('now')) {
			const value = parseInt(relativeDate.substring(3));
			const unit = relativeDate.charAt(relativeDate.length - 1);
			const date = new Date();
			
			if (unit === 'd') {
				date.setDate(date.getDate() + value);
			} else if (unit === 'm') {
				date.setMonth(date.getMonth() + value);
			} else {
				return null;
			}
			
			return date;
		}

		return null;
	}

	/**
	 * @param {Overlay} overlay
	 */
	submitAcknowledge(overlay) {
        const url = new Curl('zabbix.php', false);
        url.setArgument('action', 'popup.acknowledge.create');
		url.setArgument('output', 'json');

        const form = overlay.$dialogue.find('form');

        form.find('input[type="text"], textarea').each(function() {
            const $this = $(this);
            $this.val($this.val().trim());
        });

        const formData = new FormData(form[0]);

		overlay.xhr = sendAjaxData(url.getUrl(), {
			data: formData,
            processData: false,
            contentType: false,
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

				message_box.insertBefore(form);
			}
			else {
                // TODO: Errors in javascript console
				overlayDialogueDestroy(overlay.dialogueid);
				$.publish('widget.refresh', { overlay: overlay });
			}
		});
	}
}
