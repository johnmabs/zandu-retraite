import { Controller } from "@hotwired/stimulus";

// Bascule visuellement la carte sélectionnée (classe .selected) quand un
// radio du groupe change. Purement cosmétique, le radio reste la source
// de vérité pour la soumission du formulaire.
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
