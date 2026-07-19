<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718194107 extends AbstractMigration
{
    private const string TITLE = 'CONTRAT D\'ADHÉSION ZANDU RETRAITE';
    private const string BODY = <<<'TEXT'
Programme d'Épargne et de Protection Sociale des Commerçants des Marchés Domaniaux Connectés du Congo

Présenté par : CONGO SERVICES COMPANY (C.S.C.) SARL

ENTRE :

La structure ZANDU RETRAITE
Département d'Épargne, de Retraite et d'Inclusion Sociale des Commerçants
Ci-après dénommée « ZANDU RETRAITE »,

ET :

Le Commerçant Adhérent
Nom : {nom}
Prénom(s) : {prenom}
Téléphone : {tel}
Adresse : {adresse}
Numéro de pièce d'identité : {cni}
Identifiant client ZANDU : {id}
Date d'adhésion : {date_inscription_fr}
Ci-après dénommé « L'Adhérent »,

IL A ÉTÉ CONVENU CE QUI SUIT :

ARTICLE 1 : OBJET DU CONTRAT
Le présent contrat a pour objet l'adhésion volontaire de l'Adhérent au programme ZANDU RETRAITE destiné à :
- promouvoir l'épargne retraite ;
- faciliter l'inclusion sociale et financière ;
- assurer un suivi administratif des commerçants ;
- permettre l'accès aux services numériques et sociaux du programme.

ARTICLE 2 : ATTRIBUTION DE LA CARTE ZANDU
Après validation du dossier, l'Adhérent reçoit :
- une carte ZANDU RETRAITE ;
- un numéro d'identification personnel : {id} ;
- un compte d'épargne retraite numérique.

ARTICLE 3 : COTISATIONS
L'Adhérent s'engage à effectuer des cotisations journalières selon la formule choisie, sur une durée de {duree}.

Montant journalier choisi : {versJour}
Montant mensuel équivalent : {versMensuel}

Les paiements peuvent être effectués via :
- Mobile Money (MTN Mobile Money, Airtel Money) ;
- Carte Visa / Banque de la place ;
- Virement bancaire ;
- agence ZANDU ;
- plateforme digitale.

Répartition de chaque versement :
- Cotisation retraite : {taux_retraite}%
- Frais de gestion (CSC) : {taux_gestion}%
- Assurance sociale (CNSS) : {taux_cnss}%

Des frais d'inscription uniques de {frais_inscription} ont été perçus à la signature du présent contrat, déduits du premier versement.

Capital retraite estimé à l'échéance : {capital_estime} (montant indicatif, dépendant de la régularité effective des versements).

Catégorie d'adhésion : {categorie}

ARTICLE 4 : SERVICES FOURNIS
ZANDU RETRAITE met à disposition :
- un système d'épargne retraite ;
- une identification numérique ;
- un historique des cotisations ;
- une assistance administrative ;
- des services sociaux partenaires ;
- des outils numériques de gestion.

ARTICLE 5 : ENGAGEMENTS DE L'ADHÉRENT
L'Adhérent s'engage à :
- fournir des informations exactes ;
- respecter le règlement intérieur ;
- protéger sa carte d'adhésion ;
- maintenir des cotisations régulières ;
- informer ZANDU RETRAITE de tout changement administratif.

ARTICLE 6 : ENGAGEMENTS DE ZANDU RETRAITE
ZANDU RETRAITE s'engage à :
- sécuriser les données des adhérents ;
- assurer la transparence des opérations ;
- fournir les services prévus ;
- accompagner les commerçants dans les démarches sociales ;
- garantir la confidentialité des informations.

ARTICLE 7 : DURÉE
Le présent contrat est conclu pour une durée indéterminée à compter de sa signature.

ARTICLE 8 : SUSPENSION OU RÉSILIATION
L'adhésion peut être suspendue ou résiliée en cas :
- de fraude ;
- de fausse déclaration ;
- d'utilisation abusive des services ;
- de non-respect du règlement intérieur.

L'Adhérent peut également, à tout moment, suspendre ou résilier volontairement son adhésion en se rendant dans une agence ZANDU RETRAITE ou via son espace client en ligne.

ARTICLE 9 : PROTECTION DES DONNÉES
Les informations collectées sont utilisées exclusivement dans le cadre des activités de ZANDU RETRAITE, conformément aux lois en vigueur en République du Congo. ZANDU RETRAITE s'engage à protéger les données personnelles de l'Adhérent.

ARTICLE 10 : BÉNÉFICIAIRE
En cas de décès de l'Adhérent, les droits acquis seront transférés au bénéficiaire désigné : {beneficiaire}.

ARTICLE 11 : ACCEPTATION
En signant ce contrat, l'Adhérent reconnaît avoir pris connaissance du règlement du programme ZANDU RETRAITE et accepter l'ensemble des conditions.

PIÈCES À JOINDRE
☐ Copie de la pièce d'identité
☐ Photo d'identité
☐ Numéro de téléphone
☐ Adresse ou emplacement du commerce
☐ Frais d'adhésion

Fait à Brazzaville, le {date_inscription_fr}, en double exemplaire.

SIGNATURES

L'ADHÉRENT                                   ZANDU RETRAITE
Nom et signature :                           Nom du responsable et signature :


_______________________                     _______________________


« Préparer aujourd'hui la retraite des commerçants de demain »
TEXT;

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO contract_template (id, title, body, is_active, updated_at) VALUES (?, ?, ?, 1, NOW())',
            [Uuid::v7()->toBinary(), self::TITLE, self::BODY]
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM contract_template WHERE title = 'CONTRAT D\\'ADHÉSION ZANDU RETRAITE'");
    }
}
