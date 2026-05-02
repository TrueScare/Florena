import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["filename"];

    update(event) {
        const [file] = event.currentTarget.files;

        if (!file) {
            this.filenameTarget.textContent = "Keine Datei ausgewählt";
            return;
        }

        this.filenameTarget.textContent = file.name;
    }
}
