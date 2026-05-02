import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["taskOption", "visibleCount"];

    connect() {
        this.startInput = this.element.querySelector("#task_assignments_start_date");
        this.endInput = this.element.querySelector("#task_assignments_end_date");
        this.taskSelect = this.element.querySelector("#task_assignments_care_tasks");

        this.filter = this.filter.bind(this);
        this.startInput?.addEventListener("change", this.filter);
        this.endInput?.addEventListener("change", this.filter);

        this.filter();
    }

    disconnect() {
        this.startInput?.removeEventListener("change", this.filter);
        this.endInput?.removeEventListener("change", this.filter);
    }

    filter() {
        if (!this.taskSelect) {
            return;
        }

        const start = this.startInput?.value ? new Date(this.startInput.value) : null;
        const end = this.endInput?.value ? new Date(this.endInput.value) : null;
        let visibleCount = 0;

        this.taskOptionTargets.forEach((option) => {
            const dueDate = new Date(option.dataset.dueDate);
            const isVisible = (!start || dueDate >= start) && (!end || dueDate <= end);

            option.hidden = !isVisible;
            option.disabled = !isVisible;

            if (!isVisible) {
                option.selected = false;
            } else {
                visibleCount += 1;
            }
        });

        if (this.hasVisibleCountTarget) {
            this.visibleCountTarget.textContent = visibleCount.toString();
        }
    }

    selectVisible() {
        if (!this.taskSelect) {
            return;
        }

        this.taskOptionTargets.forEach((option) => {
            option.selected = !option.disabled;
        });
    }
}
