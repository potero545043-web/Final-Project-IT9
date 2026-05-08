import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea').forEach((textarea) => {
        textarea.addEventListener('input', () => {
            textarea.style.height = 'auto';
            textarea.style.height = `${textarea.scrollHeight}px`;
        });
    });

    document.querySelectorAll('[data-item-report-form]').forEach((form) => {
        const typeSelect = form.querySelector('[data-report-type]');
        const rewardGroup = form.querySelector('[data-reward-group]');
        const rewardInput = form.querySelector('[data-reward-input]');

        if (!typeSelect || !rewardGroup || !rewardInput) {
            return;
        }

        const toggleRewardField = () => {
            const isLostReport = typeSelect.value === 'lost';

            rewardGroup.hidden = !isLostReport;
            rewardInput.disabled = !isLostReport;

            if (!isLostReport) {
                rewardInput.value = '';
            }
        };

        toggleRewardField();
        typeSelect.addEventListener('change', toggleRewardField);
    });

    document.querySelectorAll('[data-form-stepper]').forEach((stepper) => {
        const steps = [...stepper.querySelectorAll('[data-form-step]')];
        const indicators = [...stepper.querySelectorAll('[data-step-indicator]')];
        const stepSummary = stepper.querySelector('[data-step-summary]');
        const stepInput = stepper.querySelector('[data-step-input]');
        let currentStep = Number(stepper.getAttribute('data-initial-step') || 1);

        if (!steps.length || !stepSummary || !stepInput) {
            return;
        }

        const updateStepper = () => {
            currentStep = Math.min(Math.max(currentStep, 1), steps.length);
            stepInput.value = currentStep;

            steps.forEach((step) => {
                const stepNumber = Number(step.getAttribute('data-form-step'));
                step.hidden = stepNumber !== currentStep;
            });

            indicators.forEach((indicator) => {
                const stepNumber = Number(indicator.getAttribute('data-step-indicator'));
                const marker = indicator.querySelector('[data-step-marker]');
                const state = stepNumber < currentStep
                    ? 'complete'
                    : stepNumber === currentStep
                        ? 'current'
                        : 'upcoming';

                indicator.dataset.state = state;

                if (marker) {
                    marker.textContent = state === 'complete' ? '✓' : String(stepNumber);
                }
            });

            const activeStep = steps.find((step) => Number(step.getAttribute('data-form-step')) === currentStep);
            const stepTitle = activeStep?.getAttribute('data-step-title') ?? 'Current step';
            stepSummary.textContent = `Step ${currentStep} of ${steps.length}: ${stepTitle}`;
        };

        const validateCurrentStep = () => {
            const activeStep = steps.find((step) => Number(step.getAttribute('data-form-step')) === currentStep);

            if (!activeStep) {
                return true;
            }

            const inputs = [...activeStep.querySelectorAll('input, select, textarea')];

            for (const input of inputs) {
                if (typeof input.reportValidity === 'function' && !input.reportValidity()) {
                    return false;
                }
            }

            return true;
        };

        stepper.querySelectorAll('[data-step-next]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!validateCurrentStep()) {
                    return;
                }

                currentStep += 1;
                updateStepper();
            });
        });

        stepper.querySelectorAll('[data-step-prev]').forEach((button) => {
            button.addEventListener('click', () => {
                currentStep -= 1;
                updateStepper();
            });
        });

        updateStepper();
    });

    document.querySelectorAll('[data-image-input]').forEach((input) => {
        const previewId = input.getAttribute('data-preview-target');
        const previewCard = previewId ? document.getElementById(previewId) : null;
        const previewImage = previewCard?.querySelector('[data-preview-image]');
        const previewEmpty = previewCard?.querySelector('[data-preview-empty]');

        if (!previewCard || !previewImage || !previewEmpty) {
            return;
        }

        input.addEventListener('change', () => {
            const [file] = input.files ?? [];

            if (!file) {
                if (!previewImage.getAttribute('src')) {
                    previewCard.hidden = true;
                }

                previewImage.hidden = !previewImage.getAttribute('src');
                previewEmpty.hidden = !previewImage.hidden;
                return;
            }

            const objectUrl = URL.createObjectURL(file);

            previewCard.hidden = false;
            previewImage.src = objectUrl;
            previewImage.hidden = false;
            previewEmpty.hidden = true;
        });
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const container = button.closest('.password-field-wrap');
        const input = container?.querySelector('[data-password-input]');
        const icon = button.querySelector('[data-password-toggle-icon]');
        const showLabel = button.getAttribute('data-show-label') || 'Show password';
        const hideLabel = button.getAttribute('data-hide-label') || 'Hide password';

        if (!input || !icon) {
            return;
        }

        button.addEventListener('click', () => {
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            button.setAttribute('aria-label', isHidden ? hideLabel : showLabel);
            icon.textContent = isHidden ? 'Hide' : 'Show';
        });
    });

    const deleteModalForm = document.querySelector('[data-delete-modal-form]');
    const deleteModalTitle = document.querySelector('[data-delete-modal-title]');
    const deleteModalId = document.querySelector('[data-delete-modal-id]');
    const deleteModalOwner = document.querySelector('[data-delete-modal-owner]');
    const deleteModalStatus = document.querySelector('[data-delete-modal-status]');

    if (deleteModalForm && deleteModalTitle && deleteModalId && deleteModalOwner && deleteModalStatus) {
        document.querySelectorAll('[data-delete-trigger]').forEach((button) => {
            button.addEventListener('click', () => {
                deleteModalForm.setAttribute('action', button.getAttribute('data-delete-action') || '');
                deleteModalTitle.textContent = button.getAttribute('data-delete-title') || 'this report';
                deleteModalId.textContent = `#${button.getAttribute('data-delete-id') || ''}`;
                deleteModalOwner.textContent = button.getAttribute('data-delete-owner') || 'Unknown user';
                deleteModalStatus.textContent = button.getAttribute('data-delete-status') || 'Unknown';

                window.dispatchEvent(new CustomEvent('open-modal', {
                    detail: 'report-delete-confirmation',
                }));
            });
        });
    }

    document.querySelectorAll('[data-admin-account-form]').forEach((form) => {
        const roleSelect = form.querySelector('[data-admin-role-select]');
        const warning = form.querySelector('[data-admin-role-warning]');

        if (!roleSelect || !warning) {
            return;
        }

        const toggleAdminWarning = () => {
            warning.hidden = roleSelect.value !== 'admin';
        };

        toggleAdminWarning();
        roleSelect.addEventListener('change', toggleAdminWarning);
    });
});
