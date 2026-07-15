import { Controller } from "@hotwired/stimulus";

/*
 * Filtre côté client la liste des sous-secteurs selon le secteur choisi,
 * et bascule la visibilité/obligation du champ "Précisez votre secteur"
 * quand le secteur "Autre" est sélectionné. Aucune requête réseau :
 * toutes les données nécessaires sont injectées au chargement de la page
 * via les values du contrôleur.
 */
export default class extends Controller {
    static targets = ["sector", "subSector", "customLabel", "customLabelRow"];
    static values = {
        subSectorsBySector: Object,
        otherSectorIds: Array,
    };

    connect() {
        this.update();
    }

    update() {
        const sectorId = this.sectorTarget.value;
        this.#refreshSubSectors(sectorId);
        this.#refreshCustomLabel(sectorId);
    }

    #refreshSubSectors(sectorId) {
        const select = this.subSectorTarget;
        const currentValue = select.value;
        const subSectors = this.subSectorsBySectorValue[sectorId] ?? [];

        select.innerHTML = "";

        const placeholder = document.createElement("option");
        placeholder.value = "";
        placeholder.textContent = subSectors.length
            ? "-- Sélectionner --"
            : "Sélectionnez d'abord un secteur";
        select.appendChild(placeholder);

        for (const subSector of subSectors) {
            const option = document.createElement("option");
            option.value = subSector.id;
            option.textContent = subSector.name;
            select.appendChild(option);
        }

        select.disabled = subSectors.length === 0;

        // Conserve la sélection si elle reste valide pour le nouveau secteur
        if (subSectors.some((s) => s.id === currentValue)) {
            select.value = currentValue;
        }
    }

    #refreshCustomLabel(sectorId) {
        const isOther = this.otherSectorIdsValue.includes(sectorId);

        this.customLabelRowTarget.style.display = isOther ? "" : "none";
        this.customLabelTarget.required = isOther;
        this.customLabelTarget.disabled = !isOther;
    }
}
