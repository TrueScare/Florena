import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["location", "light", "temperature", "humidity", "status"];
    static values = { url: String };

    connect() {
        this.originalOptions = Array.from(this.locationTarget.options).map((option) => ({
            value: option.value,
            text: option.textContent,
        }));

        this.refresh();
    }

    async refresh() {
        if (!this.hasRequiredValues()) {
            this.restoreOriginalOptions();
            this.setStatus("Wähle Lichtbedarf, Temperaturbedarf und Luftfeuchtigkeit aus.");
            return;
        }

        const selectedValue = this.locationTarget.value;
        const url = new URL(this.urlValue, window.location.origin);

        url.searchParams.set("light_requirement", this.lightTarget.value);
        url.searchParams.set("temperature_requirement", this.temperatureTarget.value);
        url.searchParams.set("humidity_requirement", this.humidityTarget.value);

        this.setStatus("Standortvorschläge werden geladen...");

        try {
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error();
            }

            const groups = await response.json();

            this.renderGroups(groups, selectedValue);
            this.setStatus("Standorte wurden nach Eignung sortiert.");
        } catch (error) {
            this.restoreOriginalOptions(selectedValue);
            this.setStatus("Standortvorschläge konnten nicht geladen werden.");
        }
    }

    hasRequiredValues() {
        return this.lightTarget.value && this.temperatureTarget.value && this.humidityTarget.value;
    }

    renderGroups(groups, selectedValue) {
        this.locationTarget.innerHTML = "";
        this.appendEmptyOption();

        [
            ["geeignet", "Geeignete Standorte"],
            ["teilweise geeignet", "Teilweise geeignete Standorte"],
            ["nicht geeignet", "Nicht geeignete Standorte"],
        ].forEach(([key, label]) => {
            const entries = Object.values(groups[key] || {});

            if (entries.length === 0) {
                return;
            }

            const optgroup = document.createElement("optgroup");
            optgroup.label = label;

            entries.forEach((entry) => {
                const location = entry.entity || entry;

                if (!location?.id || !location?.name) {
                    return;
                }

                const option = document.createElement("option");

                option.value = location.id;
                option.textContent = location.name;

                optgroup.appendChild(option);
            });

            this.locationTarget.appendChild(optgroup);
        });

        this.locationTarget.value = selectedValue;
    }

    appendEmptyOption() {
        const option = document.createElement("option");
        option.value = "";
        option.textContent = "Kein Standort";
        this.locationTarget.appendChild(option);
    }

    restoreOriginalOptions(selectedValue = this.locationTarget.value) {
        this.locationTarget.innerHTML = "";

        this.originalOptions.forEach((originalOption) => {
            const option = document.createElement("option");
            option.value = originalOption.value;
            option.textContent = originalOption.text;
            this.locationTarget.appendChild(option);
        });

        this.locationTarget.value = selectedValue;
    }

    setStatus(message) {
        if (this.hasStatusTarget) {
            this.statusTarget.textContent = message;
        }
    }
}
