import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["card", "counter", "bar", "complete", "progress", "status"];

    connect() {
        this.currentIndex = 0;
        this.total = this.cardTargets.length;
        this.updateProgress();
    }

    async done(event) {
        const button = event.currentTarget;
        const url = button.dataset.careTaskUrl;

        button.disabled = true;
        button.textContent = "...";
        this.clearStatus();

        try {
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error();
            }

            this.showNextCard();
        } catch (error) {
            button.disabled = false;
            button.textContent = "Erledigt";
            this.showStatus("Die Aufgabe konnte nicht erledigt werden.");
        }
    }

    showNextCard() {
        this.cardTargets[this.currentIndex]?.classList.add("hidden");
        this.currentIndex += 1;

        if (this.currentIndex >= this.total) {
            this.showCompleteState();
            return;
        }

        this.cardTargets[this.currentIndex]?.classList.remove("hidden");
        this.updateProgress();
    }

    showCompleteState() {
        this.progressTarget.classList.add("hidden");
        this.completeTarget.classList.remove("hidden");
        this.updateProgress(true);
    }

    updateProgress(isComplete = false) {
        if (this.total === 0) {
            return;
        }

        const currentTaskNumber = Math.min(this.currentIndex + 1, this.total);
        const progressValue = isComplete ? 100 : (currentTaskNumber / this.total) * 100;

        this.counterTarget.textContent = `Aufgabe ${currentTaskNumber} von ${this.total}`;
        this.barTarget.style.width = `${progressValue}%`;
    }

    clearStatus() {
        this.statusTarget.textContent = "";
        this.statusTarget.classList.add("hidden");
    }

    showStatus(message) {
        this.statusTarget.textContent = message;
        this.statusTarget.classList.remove("hidden");
    }
}
