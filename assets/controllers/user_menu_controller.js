import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["dropdown", "button", "switch", "status"];

    connect() {
        this.closeOnOutsideClick = this.closeOnOutsideClick.bind(this);
        document.addEventListener("click", this.closeOnOutsideClick);

        this.setExpanded(false);
        this.initialMinimalMode = this.switchTarget.getAttribute("aria-checked") === "true";
    }

    disconnect() {
        document.removeEventListener("click", this.closeOnOutsideClick);
    }

    toggle(event) {
        event.stopPropagation();

        const isOpen = !this.dropdownTarget.classList.toggle("hidden");
        this.setExpanded(isOpen);
    }

    close() {
        this.dropdownTarget.classList.add("hidden");
        this.setExpanded(false);
    }

    closeOnOutsideClick(event) {
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }

    async toggleMinimalMode(event) {
        event.stopPropagation();

        const previousState = this.isMinimalModeEnabled();
        this.setLoading(true);
        this.clearStatus();

        try {
            const response = await fetch("/api/minimalism");

            if (!response.ok) {
                throw new Error();
            }

            const data = await response.json();
            const isEnabled = Boolean(data.user?.is_minimal_mode);

            this.setMinimalMode(isEnabled);
            this.initialMinimalMode = isEnabled;
        } catch (error) {
            this.setMinimalMode(previousState);
            this.showStatus("Minimalismus-Modus konnte nicht aktualisiert werden.", true);
        } finally {
            this.setLoading(false);
        }
    }

    isMinimalModeEnabled() {
        return this.switchTarget.getAttribute("aria-checked") === "true";
    }

    setMinimalMode(isEnabled) {
        this.switchTarget.setAttribute("aria-checked", String(isEnabled));
        this.switchTarget.classList.toggle("bg-primary", isEnabled);
        this.switchTarget.classList.toggle("bg-outline-variant", !isEnabled);

        const thumb = this.switchTarget.querySelector("[data-user-menu-thumb]");
        thumb?.classList.toggle("translate-x-5", isEnabled);
    }

    setLoading(isLoading) {
        this.switchTarget.disabled = isLoading;
        this.switchTarget.classList.toggle("opacity-60", isLoading);
        this.switchTarget.classList.toggle("cursor-wait", isLoading);
    }

    setExpanded(isExpanded) {
        this.buttonTarget.setAttribute("aria-expanded", String(isExpanded));
    }

    clearStatus() {
        this.statusTarget.textContent = "";
        this.statusTarget.classList.add("hidden");
        this.statusTarget.classList.remove("text-error");
    }

    showStatus(message, isError = false) {
        this.statusTarget.textContent = message;
        this.statusTarget.classList.remove("hidden");
        this.statusTarget.classList.toggle("text-error", isError);
    }
}
