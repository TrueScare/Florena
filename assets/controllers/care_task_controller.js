import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        console.log("CareTaskController connected");
    }

    async done(event) {
        const button = event.currentTarget;
        const url = button.dataset.careTaskUrl;
        const card = button.closest(".care-task-card");

        button.disabled = true;
        button.textContent = "...";

        try {
            const response = await fetch(url);

            if (!response.ok) throw new Error();

            card?.remove();
        } catch (e) {
            button.disabled = false;
            button.textContent = "Erledigen";
        }
    }
}
