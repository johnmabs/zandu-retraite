import { Controller } from "@hotwired/stimulus";

// Aperçu client-side uniquement — reproduit la formule déjà utilisée côté
// serveur (PayslipCalculator/MemberFinancialCalculator) pour donner un
// retour immédiat pendant la saisie. Les montants réellement appliqués sont
// toujours recalculés côté serveur au moment de générer un bulletin/contrat.
export default class extends Controller {
    static targets = [
        "dailyAmount",
        "duration",
        "box",
        "pensionDay",
        "managementDay",
        "cnssDay",
        "capital",
    ];
    static values = {
        pensionRate: Number,
        managementRate: Number,
        cnssRate: Number,
    };

    recalculate() {
        const daily = parseFloat(this.dailyAmountTarget.value);
        const years = parseInt(this.durationTarget.value, 10);

        if (!daily || !years) {
            this.boxTarget.style.display = "none";
            return;
        }

        this.boxTarget.style.display = "";

        const pensionDay = (daily * this.pensionRateValue) / 100;
        const managementDay = (daily * this.managementRateValue) / 100;
        const cnssDay = (daily * this.cnssRateValue) / 100;
        const capital = daily * 365 * years * (this.pensionRateValue / 100);

        const fmt = (n) => Math.round(n).toLocaleString("fr-FR") + " FCFA";

        this.pensionDayTarget.textContent = fmt(pensionDay);
        this.managementDayTarget.textContent = fmt(managementDay);
        this.cnssDayTarget.textContent = fmt(cnssDay);
        this.capitalTarget.textContent = fmt(capital);
    }
}
