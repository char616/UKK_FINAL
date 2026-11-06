document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-scp-sidebar]');
    const backdrop = document.querySelector('[data-scp-sidebar-backdrop]');
    const openButton = document.querySelector('[data-scp-sidebar-open]');
    const closeButtons = document.querySelectorAll('[data-scp-sidebar-close]');

    if (sidebar && backdrop && openButton) {
        let previouslyFocusedElement = null;
        const mobileSidebar = window.matchMedia('(max-width: 767.98px)');

        const setOpen = (isOpen, restoreFocus = true) => {
            sidebar.classList.toggle('is-open', isOpen);
            backdrop.classList.toggle('is-visible', isOpen);
            openButton.setAttribute('aria-expanded', String(isOpen));
            sidebar.setAttribute('aria-hidden', String(!isOpen && mobileSidebar.matches));
            document.body.classList.toggle('overflow-hidden', isOpen);

            if (isOpen) {
                previouslyFocusedElement = document.activeElement;
                sidebar.querySelector('.scp-sidebar-nav a')?.focus();
            } else if (restoreFocus && previouslyFocusedElement instanceof HTMLElement) {
                previouslyFocusedElement.focus();
            }
        };

        sidebar.setAttribute('aria-hidden', String(mobileSidebar.matches));
        openButton.addEventListener('click', () => setOpen(true));
        closeButtons.forEach((button) => button.addEventListener('click', () => setOpen(false)));
        sidebar.addEventListener('click', (event) => {
            if (event.target.closest('a') && window.matchMedia('(max-width: 767px)').matches) {
                setOpen(false, false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
                setOpen(false);
            }
        });

        mobileSidebar.addEventListener('change', () => setOpen(false, false));
    }

    const scheduleForm = document.querySelector('[data-schedule-form]');

    if (!scheduleForm || scheduleForm.dataset.scpScheduleBound === 'true') {
        return;
    }
    scheduleForm.dataset.scpScheduleBound = 'true';

    const scheduleRows = scheduleForm.querySelector('[data-schedule-rows]');
    const rowTemplate = document.querySelector('[data-schedule-row-template]');

    if (!scheduleRows || !rowTemplate) {
        return;
    }

    const occupancyNode = document.querySelector('[data-schedule-occupancy]');
    const reservedSchedules = occupancyNode ? JSON.parse(occupancyNode.textContent) : [];

    const sessionsNode = document.querySelector('[data-schedule-sessions]');
    const sessionsByDay = sessionsNode ? JSON.parse(sessionsNode.textContent) : {};

    const updateRowSessionConstraint = (row) => {
        const startSelect = row.querySelector('[data-session-start]');
        const endSelect = row.querySelector('[data-session-end]');
        const previewEl = row.querySelector('[data-session-preview]');

        if (!startSelect || !endSelect) {
            return;
        }

        const startOpt = startSelect.selectedOptions[0];
        const startOrder = parseInt(startOpt?.dataset.order || '0', 10);

        if (startOrder > 0) {
            Array.from(endSelect.options).forEach((opt) => {
                if (!opt.value) return;
                const endOrder = parseInt(opt.dataset.order || '0', 10);
                const isInvalid = endOrder < startOrder;
                opt.disabled = isInvalid;
                opt.hidden = isInvalid;
            });

            const currentEndOpt = endSelect.selectedOptions[0];
            const currentEndOrder = parseInt(currentEndOpt?.dataset.order || '0', 10);

            if (!endSelect.value || currentEndOrder < startOrder) {
                const sameSessOpt = Array.from(endSelect.options).find(
                    (opt) => parseInt(opt.dataset.order || '0', 10) === startOrder
                );
                if (sameSessOpt) {
                    endSelect.value = sameSessOpt.value;
                }
            }
        }

        if (previewEl) {
            if (startSelect.value && endSelect.value) {
                const sOpt = startSelect.selectedOptions[0];
                const eOpt = endSelect.selectedOptions[0];
                const sName = sOpt?.dataset.nama || startSelect.value;
                const eName = eOpt?.dataset.nama || endSelect.value;

                if (sName === eName) {
                    previewEl.textContent = `${sName} (${startSelect.value} - ${endSelect.value})`;
                } else {
                    previewEl.textContent = `${sName} s/d ${eName} (${startSelect.value} - ${endSelect.value})`;
                }
            } else {
                previewEl.textContent = 'Silakan pilih sesi mulai dan selesai.';
            }
        }
    };

    const populateRowSessions = (row) => {
        const daySelect = row.querySelector('select[name$="[day]"]');
        const startSelect = row.querySelector('[data-session-start]');
        const endSelect = row.querySelector('[data-session-end]');

        if (!daySelect || !startSelect || !endSelect) {
            return;
        }

        const day = daySelect.value || 'Senin';
        const sessions = sessionsByDay[day] || sessionsByDay['Senin'] || [];

        const currentStart = startSelect.value;
        const currentEnd = endSelect.value;

        startSelect.innerHTML = '<option value="">-- Pilih Sesi Mulai --</option>';
        sessions.forEach((s) => {
            const opt = document.createElement('option');
            opt.value = s.jam_mulai;
            opt.dataset.nama = s.nama_sesi;
            opt.dataset.order = String(s.urutan);
            opt.dataset.end = s.jam_selesai;
            opt.textContent = `${s.nama_sesi} (Mulai ${s.jam_mulai})`;
            startSelect.appendChild(opt);
        });

        endSelect.innerHTML = '<option value="">-- Pilih Sesi Selesai --</option>';
        sessions.forEach((s) => {
            const opt = document.createElement('option');
            opt.value = s.jam_selesai;
            opt.dataset.nama = s.nama_sesi;
            opt.dataset.order = String(s.urutan);
            opt.dataset.start = s.jam_mulai;
            opt.textContent = `${s.nama_sesi} (Selesai ${s.jam_selesai})`;
            endSelect.appendChild(opt);
        });

        if (currentStart && Array.from(startSelect.options).some((o) => o.value === currentStart)) {
            startSelect.value = currentStart;
        }
        if (currentEnd && Array.from(endSelect.options).some((o) => o.value === currentEnd)) {
            endSelect.value = currentEnd;
        }

        updateRowSessionConstraint(row);
    };

    const refreshScheduleRows = () => {
        const rows = scheduleRows.querySelectorAll('[data-schedule-row]');

        rows.forEach((row, index) => {
            const num = String(index + 1);
            const rowNumEl = row.querySelector('[data-row-number]');
            if (rowNumEl) rowNumEl.textContent = num;
            const removeNumEl = row.querySelector('[data-remove-row-number]');
            if (removeNumEl) removeNumEl.textContent = num;

            row.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace(/schedules\[\d+\]/, `schedules[${index}]`);
            });
            const removeBtn = row.querySelector('[data-remove-schedule-row]');
            if (removeBtn) {
                removeBtn.hidden = index === 0;
            }
        });

        rows.forEach((row) => {
            const roomSelect = row.querySelector('select[name$="[room_id]"]');
            const day = row.querySelector('select[name$="[day]"]')?.value;
            const round = row.querySelector('select[name$="[round]"]')?.value;
            const startTime = row.querySelector('[name$="[start_time]"]')?.value;
            const endTime = row.querySelector('[name$="[end_time]"]')?.value;

            if (!roomSelect) {
                return;
            }

            Array.from(roomSelect.options).forEach((option) => {
                if (!option.value) {
                    return;
                }

                const hasTimeRange = day && round && startTime && endTime;
                const conflictsWith = (schedule) => schedule.day === day
                    && String(schedule.round) === String(round)
                    && schedule.start_time < endTime
                    && schedule.end_time > startTime;
                const reservedInExistingSchedule = hasTimeRange && reservedSchedules.some((schedule) => (
                    String(schedule.room_id) === option.value && conflictsWith(schedule)
                ));
                const reservedInAnotherRow = hasTimeRange && Array.from(rows).some((otherRow) => {
                    if (otherRow === row) {
                        return false;
                    }

                    const otherRoom = otherRow.querySelector('select[name$="[room_id]"]')?.value;
                    const otherSchedule = {
                        day: otherRow.querySelector('select[name$="[day]"]')?.value,
                        round: otherRow.querySelector('select[name$="[round]"]')?.value,
                        start_time: otherRow.querySelector('[name$="[start_time]"]')?.value,
                        end_time: otherRow.querySelector('[name$="[end_time]"]')?.value,
                    };

                    return otherRoom === option.value
                        && otherSchedule.start_time
                        && otherSchedule.end_time
                        && conflictsWith(otherSchedule);
                });
                const unavailable = reservedInExistingSchedule || reservedInAnotherRow;

                option.disabled = unavailable;
                option.hidden = unavailable;

                if (unavailable && roomSelect.value === option.value) {
                    roomSelect.value = '';
                }
            });
        });
    };

    document.querySelectorAll('[data-add-schedule-row]').forEach((button) => {
        button.addEventListener('click', () => {
            const index = scheduleRows.querySelectorAll('[data-schedule-row]').length;
            const row = rowTemplate.content.cloneNode(true);

            row.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replaceAll('__INDEX__', String(index));
            });
            scheduleRows.append(row);
            const newRow = scheduleRows.querySelectorAll('[data-schedule-row]')[index];
            if (newRow) {
                populateRowSessions(newRow);
            }
            refreshScheduleRows();
        });
    });

    scheduleRows.addEventListener('change', (event) => {
        const row = event.target.closest('[data-schedule-row]');
        if (event.target.matches('select[name$="[day]"]')) {
            if (row) populateRowSessions(row);
        } else if (event.target.matches('[data-session-start], [data-session-end]')) {
            if (row) updateRowSessionConstraint(row);
        }
        refreshScheduleRows();
    });

    scheduleRows.addEventListener('input', (event) => {
        refreshScheduleRows();
    });

    scheduleRows.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-schedule-row]');

        if (removeButton && scheduleRows.querySelectorAll('[data-schedule-row]').length > 1) {
            removeButton.closest('[data-schedule-row]').remove();
            refreshScheduleRows();
        }
    });

    scheduleRows.querySelectorAll('[data-schedule-row]').forEach((row) => {
        updateRowSessionConstraint(row);
    });
    refreshScheduleRows();
});
