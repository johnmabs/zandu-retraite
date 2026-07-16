import { Controller } from "@hotwired/stimulus";

const PHONE_METHODS = ["mtn_momo", "airtel_money"];

export default class extends Controller {
    static targets = ["method", "phoneRow", "referenceRow"];

    connect() {
        this.update();
    }

    update() {
        const checked = this.methodTargets.find((el) => el.checked);
        const method = checked?.value;

        this.phoneRowTarget.style.display = PHONE_METHODS.includes(method)
            ? ""
            : "none";
        this.referenceRowTarget.style.display =
            "bank_transfer" === method ? "" : "none";
    }
}
