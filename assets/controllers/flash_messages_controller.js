import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["message"];
    static values = {
        timeout: { type: Number, default: 5000 }
    };

    connect() {
        this.dismiss = this.dismiss.bind(this);
        this.timeoutId = window.setTimeout(this.dismiss, this.timeoutValue);
    }

    disconnect() {
        if (this.timeoutId) {
            window.clearTimeout(this.timeoutId);
        }

        if (this.removeId) {
            window.clearTimeout(this.removeId);
        }
    }

    dismiss() {
        this.messageTargets.forEach((message) => {
            this.hideMessage(message);
        });

        this.removeId = window.setTimeout(() => {
            this.removeWrapperIfEmpty();
        }, 300);
    }

    close(event) {
        const message = event.currentTarget.closest('[data-flash-messages-target="message"]');

        if (!message) {
            return;
        }

        this.hideMessage(message);

        window.setTimeout(() => {
            message.remove();
            this.removeWrapperIfEmpty();
        }, 300);
    }

    hideMessage(message) {
        message.classList.add("opacity-0", "-translate-y-2");
    }

    removeWrapperIfEmpty() {
        if (this.messageTargets.length === 0) {
            this.element.remove();
        }
    }
}
