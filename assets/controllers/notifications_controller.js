import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dropdown'];

    async toggle() {
        this.dropdownTarget.classList.toggle('hidden');

        if (this.dropdownTarget.classList.contains('hidden')) {
            return;
        }

        try {
            const response = await fetch('/notifications');

            if (!response.ok) {
                throw new Error();
            }

            this.dropdownTarget.innerHTML = await response.text();
        } catch (error) {
            this.dropdownTarget.innerHTML = '<p class="p-3 text-sm text-error">Fehler beim Laden.</p>';
        }
    }
}
