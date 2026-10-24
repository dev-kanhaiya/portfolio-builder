document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-copy-link]').forEach(function (button) {
        const originalLabel = button.textContent.trim();

        button.addEventListener('click', async function () {
            const link = button.dataset.copyLink;

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(link);
                } else {
                    const input = document.createElement('textarea');
                    input.value = link;
                    input.setAttribute('readonly', '');
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.append(input);
                    input.select();

                    const copied = document.execCommand('copy');
                    input.remove();

                    if (!copied) throw new Error('Could not copy the link.');
                }

                button.textContent = 'Link copied';
            } catch (error) {
                button.textContent = 'Copy failed';
            }

            window.setTimeout(function () {
                button.textContent = originalLabel;
            }, 1800);
        });
    });
});
