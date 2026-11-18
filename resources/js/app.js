import './bootstrap';

function showToast(message) {
    document.querySelectorAll('[data-toast]').forEach((el) => el.remove());

    const toast = document.createElement('div');
    toast.setAttribute('data-toast', '');
    toast.setAttribute('data-flash-auto-hide', '');
    toast.className = 'fixed right-4 top-4 z-50 max-w-sm rounded-2xl bg-mivoo-ink px-4 py-3 text-sm text-white shadow-lg';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 3500);
}

async function shareReferral(button) {
    const url = button.dataset.shareUrl;
    const title = button.dataset.shareTitle || 'Mivoo';
    const text = button.dataset.shareText || '';

    if (!url) {
        return;
    }

    try {
        if (navigator.share) {
            await navigator.share({ title, text, url });
            return;
        }

        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(url);
            showToast('Lien de parrainage copié');
            return;
        }

        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        input.remove();
        showToast('Lien de parrainage copié');
    } catch (error) {
        if (error?.name !== 'AbortError') {
            showToast('Impossible de partager pour le moment');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-toast-dismiss]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('[data-toast]')?.remove();
        });
    });

    const flash = document.querySelector('[data-flash-auto-hide]');
    if (flash) {
        setTimeout(() => flash.remove(), 4500);
    }

    document.querySelectorAll('[data-share-referral]').forEach((button) => {
        button.addEventListener('click', () => shareReferral(button));
    });
});

window.Mivoo = {
    async fetchJson(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                ...(options.headers || {}),
            },
            ...options,
        });

        if (!response.ok) {
            throw new Error(`Requête échouée (${response.status})`);
        }

        return response.json();
    },
};
