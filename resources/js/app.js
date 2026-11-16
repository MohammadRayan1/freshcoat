/**
 * Quote form: swaps size options when the job changes and shows a typical price range.
 * "Something else" switches to a custom job: no size or estimate, the homeowner describes it.
 */
function money(cents) {
    return '$' + Math.round(cents / 100).toLocaleString('en-US');
}

function initQuoteForm(form) {
    const services = JSON.parse(form.dataset.services || '[]');
    const job = form.querySelector('[name=service_id]');
    const size = form.querySelector('[name=size]');
    const paint = form.querySelector('[name=customer_supplies_paint]');
    const output = form.querySelector('[data-estimate]');
    const outputLabel = form.querySelector('[data-estimate-label]');
    const customTitle = form.querySelector('[name=custom_title]');
    const details = form.querySelector('details.more');

    const isCustom = () => job.value === 'custom';
    const current = () => services.find((s) => String(s.id) === job.value) || services[0];

    function toggleCustom() {
        const custom = isCustom();
        form.querySelectorAll('[data-custom-only]').forEach((el) => { el.hidden = !custom; });
        form.querySelectorAll('[data-preset-only]').forEach((el) => { el.hidden = custom; });
        customTitle.disabled = !custom;
        customTitle.required = custom;
        size.disabled = custom;
        if (custom && details) {
            details.open = true;
        }
    }

    function fillSizes() {
        const service = current();
        const previous = size.value;
        size.innerHTML = '';
        service.sizes.forEach((option, index) => size.add(new Option(option.label, index)));
        size.value = previous && service.sizes[previous] ? previous : Math.min(1, service.sizes.length - 1);
    }

    function showEstimate() {
        if (isCustom()) {
            outputLabel.textContent = 'Price';
            output.textContent = 'Painters will quote';
            return;
        }
        outputLabel.textContent = 'Typical range';
        const service = current();
        const option = service.sizes[size.value] || service.sizes[0];
        const mid = service.price * option.multiplier * (paint && paint.value === '1' ? 0.85 : 1);
        output.textContent = `${money(Math.round((mid * 0.85) / 1000) * 1000)} – ${money(Math.round((mid * 1.3) / 1000) * 1000)}`;
    }

    job.addEventListener('change', () => {
        toggleCustom();
        if (!isCustom()) {
            fillSizes();
        }
        showEstimate();
    });
    size.addEventListener('change', showEstimate);
    paint?.addEventListener('change', showEstimate);

    if (!size.options.length) {
        fillSizes();
    }
    toggleCustom();
    showEstimate();
}

/**
 * Photo/video picker: previews the chosen files and catches too many or too-large files before upload.
 */
function initMediaPicker(form) {
    const input = form.querySelector('input[type=file][name="media[]"]');
    const previews = form.querySelector('[data-media-previews]');
    if (!input || !previews) {
        return;
    }
    const maxFiles = Number(input.dataset.maxFiles);
    const maxBytes = Number(input.dataset.maxMb) * 1024 * 1024;

    input.addEventListener('change', () => {
        previews.querySelectorAll('img, video').forEach((el) => URL.revokeObjectURL(el.src));
        previews.replaceChildren();
        const files = Array.from(input.files);

        let problem = '';
        if (files.length > maxFiles) {
            problem = `Pick up to ${maxFiles} files.`;
        } else if (files.some((file) => file.size > maxBytes)) {
            problem = `Each file must be under ${input.dataset.maxMb} MB. Try a shorter video.`;
        }
        if (problem) {
            input.value = '';
            const message = document.createElement('p');
            message.className = 'error';
            message.setAttribute('role', 'alert');
            message.textContent = problem;
            previews.append(message);
            return;
        }

        files.forEach((file) => {
            let el;
            if (file.type.startsWith('video/')) {
                el = document.createElement('video');
                el.muted = true;
                el.preload = 'metadata';
                el.src = URL.createObjectURL(file);
            } else if (['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                el = document.createElement('img');
                el.alt = file.name;
                el.src = URL.createObjectURL(file);
            } else {
                el = document.createElement('span');
                el.className = 'media-file';
                el.textContent = file.name;
            }
            previews.append(el);
        });
    });

    form.addEventListener('submit', () => {
        if (input.files.length) {
            const button = form.querySelector('button[type=submit]');
            button.disabled = true;
            button.textContent = 'Uploading…';
        }
    });
}

document.querySelectorAll('[data-quote-form]').forEach((form) => {
    initQuoteForm(form);
    initMediaPicker(form);
});
