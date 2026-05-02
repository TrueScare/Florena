import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["dropdown", "badge"];

    connect() {
        this.closeOnOutsideClick = this.closeOnOutsideClick.bind(this);
        document.addEventListener("click", this.closeOnOutsideClick);

        this.loadCount();
    }

    disconnect() {
        document.removeEventListener("click", this.closeOnOutsideClick);
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

    async toggle(event) {
        event.stopPropagation();

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
        } catch (error) {
            this.dropdownTarget.innerHTML =
                '<p class="p-3 text-sm text-error">Fehler beim Laden.</p>';
            return;
        }

        try {
            await this.markVisibleNotificationsAsRead();
        } finally {
            await this.loadCount();
        }
    }

    async markVisibleNotificationsAsRead() {
        const notifications = this.dropdownTarget.querySelectorAll("[data-notification-id]");

        if (notifications.length === 0) {
            return;
        }

        const requests = Array.from(notifications, (notification) => {
            const notificationId = notification.dataset.notificationId;

            return fetch(`/api/notification/${notificationId}/read`, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            }).then((response) => {
                if (!response.ok) {
                    throw new Error();
                }
            });
        });

        await Promise.all(requests);
    }

    close() {
        this.dropdownTarget.classList.add("hidden");
    }

    closeOnOutsideClick(event) {
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }
}
