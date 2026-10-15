document.addEventListener('DOMContentLoaded', () => {
    const reportForm = document.querySelector('[data-report-generator]');
    if (!reportForm) {
        return;
    }

    const role = reportForm.dataset.reportRole || document.body.dataset.portal;
    const typeSelect = reportForm.querySelector('[data-report-type]');
    const startInput = reportForm.querySelector('[data-report-from]');
    const endInput = reportForm.querySelector('[data-report-to]');
    const statusBox = reportForm.querySelector('[data-report-status]');
    const submitButton = reportForm.querySelector('[data-report-submit]');

    const optionMap = {
        farmer: [
            { value: 'sales', label: 'Sales' },
            { value: 'products', label: 'Products' },
            { value: 'orders', label: 'Orders' },
            { value: 'income', label: 'Income' },
        ],
        buyer: [
            { value: 'purchases', label: 'Purchases' },
            { value: 'status', label: 'Order status' },
            { value: 'payments', label: 'Payments' },
            { value: 'spending', label: 'Spending' },
        ],
        rider: [
            { value: 'deliveries', label: 'Deliveries' },
            { value: 'status', label: 'Delivery status' },
            { value: 'earnings', label: 'Earnings' },
            { value: 'performance', label: 'Performance' },
        ],
    };

    if (typeSelect) {
        typeSelect.innerHTML = optionMap[role]
            ?.map((option) => `<option value="${option.value}">${option.label}</option>`)
            .join('') ?? '<option value="">No report available</option>';
    }

    const setStatus = (message, isError = false) => {
        if (!statusBox) {
            return;
        }

        statusBox.textContent = message;
        statusBox.style.color = isError ? '#b42318' : '#475569';
    };

    const downloadReport = async () => {
        if (!typeSelect || !startInput || !endInput || !submitButton) {
            return;
        }

        const type = typeSelect.value;
        const params = new URLSearchParams({ type });

        if (startInput.value) {
            params.set('start_date', startInput.value);
        }

        if (endInput.value) {
            params.set('end_date', endInput.value);
        }

        if (startInput.value && endInput.value && new Date(endInput.value) < new Date(startInput.value)) {
            setStatus('End date must be on or after the start date.', true);
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Generating…';
        setStatus('Preparing your PDF report…');

        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${API_BASE_URL}/reports/${role}?${params.toString()}`, {
                headers: {
                    Accept: 'application/pdf',
                    ...(token ? { Authorization: `Bearer ${token}` } : {}),
                },
            });

            if (!response.ok) {
                const body = await response.json().catch(() => null);
                const message = body?.message || 'Unable to generate the PDF report.';
                throw new Error(message);
            }

            const blob = await response.blob();
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.href = url;
            link.download = `${role}-${type}-report.pdf`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
            setStatus('PDF download started successfully.');
        } catch (error) {
            setStatus(error.message || 'Unable to download the report.', true);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Download PDF';
        }
    };

    if (submitButton) {
        submitButton.addEventListener('click', (event) => {
            event.preventDefault();
            downloadReport();
        });
    }
});
