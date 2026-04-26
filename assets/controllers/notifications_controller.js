import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["dropdown", "badge"];

    connect() {
        this.loadCount();
    }

    async loadCount() {
        try {
            const response = await fetch("/notifications/count");

            if (!response.ok) {
                throw new Error();
            }

            const data = await response.json();

            if (data.count > 0) {
                this.badgeTarget.classList.remove("hidden");
            } else {
                this.badgeTarget.classList.add("hidden");
            }
        } catch (error) {
            this.badgeTarget.classList.add("hidden");
        }
    }

    async toggle() {
        this.dropdownTarget.classList.toggle("hidden");

        if (this.dropdownTarget.classList.contains("hidden")) {
            return;
        }

        try {
            const response = await fetch("/notifications");

            if (!response.ok) {
                throw new Error();
            }

            this.dropdownTarget.innerHTML = await response.text();

            this.badgeTarget.classList.add("hidden");
        } catch (error) {
            this.dropdownTarget.innerHTML =
                '<p class="p-3 text-sm text-error">Fehler beim Laden.</p>';
        }
    }
}
