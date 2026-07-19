import { Controller } from "@hotwired/stimulus";

// Reproduit showToast() du proto (apparition, disparition après 3s), étendu
// pour gérer plusieurs toasts empilés au lieu d'un seul élément #toast fixe.
export default class extends Controller {
    static targets = ["item"];

    connect() {
        this.itemTargets.forEach((el, index) => {
            requestAnimationFrame(() => el.classList.add("show"));
            setTimeout(() => this.dismiss(el), 10000 + index * 300);
        });
    }

    dismiss(el) {
        el.classList.remove("show");
        setTimeout(() => el.remove(), 200);
    }
}
