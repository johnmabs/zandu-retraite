import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["card"];

    connect() {
        this.refresh();
    }

    refresh() {
        this.cardTargets.forEach((card) => {
            const input = card.querySelector("input[type=radio]");
            card.classList.toggle("selected", input?.checked ?? false);
        });
    }
}
