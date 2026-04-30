import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["dropdown"];

    connect() {
        this.closeOnOutsideClick = this.closeOnOutsideClick.bind(this);
        document.addEventListener("click", this.closeOnOutsideClick);
    }

    disconnect() {
        document.removeEventListener("click", this.closeOnOutsideClick);
    }

    toggle(event) {
        event.stopPropagation();
        this.dropdownTarget.classList.toggle("hidden");
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
